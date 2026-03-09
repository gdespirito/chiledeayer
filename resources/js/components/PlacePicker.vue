<script setup lang="ts">
import L from 'leaflet';
import { MapPin, Search } from 'lucide-vue-next';
import { nextTick, onBeforeUnmount, ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useLeafletIcons } from '@/composables/useLeafletIcons';
import type { Place } from '@/types';

type Props = {
    modelValue: number | null;
    currentPlace?: Place | null;
};

type GooglePrediction = {
    place_id: string;
    description: string;
    structured_formatting: {
        main_text: string;
        secondary_text: string;
    };
};

type EmittedPlace = {
    id: number;
};

withDefaults(defineProps<Props>(), {
    currentPlace: null,
});

const emit = defineEmits<{
    'update:modelValue': [value: number | null];
    placed: [place: EmittedPlace];
}>();

// Mode: 'idle' | 'search' | 'google'
const mode = ref<'idle' | 'search' | 'google'>('idle');

// Internal place search
const internalQuery = ref('');
const internalResults = ref<Place[]>([]);
const internalLoading = ref(false);
let internalTimer: ReturnType<typeof setTimeout> | null = null;

watch(internalQuery, (query) => {
    if (internalTimer) clearTimeout(internalTimer);
    if (query.length < 2) {
        internalResults.value = [];
        return;
    }
    internalLoading.value = true;
    internalTimer = setTimeout(async () => {
        const res = await fetch(
            `/api/places/search?query=${encodeURIComponent(query)}`,
        );
        internalResults.value = await res.json();
        internalLoading.value = false;
    }, 300);
});

function selectExistingPlace(place: Place): void {
    emit('update:modelValue', place.id);
    emit('placed', { id: place.id });
    close();
}

// Google Places search
const googleQuery = ref('');
const googleResults = ref<GooglePrediction[]>([]);
const googleLoading = ref(false);
let googleTimer: ReturnType<typeof setTimeout> | null = null;

watch(googleQuery, (query) => {
    if (googleTimer) clearTimeout(googleTimer);
    if (query.length < 2) {
        googleResults.value = [];
        return;
    }
    googleLoading.value = true;
    googleTimer = setTimeout(async () => {
        const res = await fetch(
            `/api/google-places/autocomplete?query=${encodeURIComponent(query)}`,
        );
        const data = await res.json();
        googleResults.value = data.predictions ?? [];
        googleLoading.value = false;
    }, 300);
});

// Confirmation flow: map → name
const showMap = ref(false);
const showNameStep = ref(false);
const editableName = ref('');
const mapContainer = ref<HTMLElement | null>(null);
let map: L.Map | null = null;
let marker: L.Marker | null = null;
const selectedGoogle = ref<{
    google_place_id: string;
    name: string;
    latitude: number;
    longitude: number;
    city: string;
    region: string;
    country: string;
} | null>(null);
const creatingPlace = ref(false);

async function selectGooglePrediction(
    prediction: GooglePrediction,
): Promise<void> {
    const res = await fetch(
        `/api/google-places/details?place_id=${encodeURIComponent(prediction.place_id)}`,
    );
    const data = await res.json();
    const result = data.result;

    if (!result?.geometry?.location) return;

    const components = result.address_components ?? [];
    const city =
        components.find((c: { types: string[] }) =>
            c.types.includes('locality'),
        )?.long_name ?? '';
    const region =
        components.find((c: { types: string[] }) =>
            c.types.includes('administrative_area_level_1'),
        )?.long_name ?? '';
    const country =
        components.find((c: { types: string[] }) => c.types.includes('country'))
            ?.long_name ?? 'Chile';

    selectedGoogle.value = {
        google_place_id: prediction.place_id,
        name: result.name ?? prediction.structured_formatting.main_text,
        latitude: result.geometry.location.lat,
        longitude: result.geometry.location.lng,
        city,
        region,
        country,
    };

    googleResults.value = [];
    showMap.value = true;

    await nextTick();
    initMap(selectedGoogle.value.latitude, selectedGoogle.value.longitude);
}

function initMap(lat: number, lng: number): void {
    useLeafletIcons();
    if (!mapContainer.value) return;
    if (map) {
        map.remove();
        map = null;
    }

    // Force container to have explicit pixel width before Leaflet measures it
    const containerWidth = mapContainer.value.parentElement?.offsetWidth;
    if (containerWidth) {
        mapContainer.value.style.width = `${containerWidth}px`;
    }

    map = L.map(mapContainer.value).setView([lat, lng], 16);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
    }).addTo(map);

    marker = L.marker([lat, lng], { draggable: true }).addTo(map);
    marker.on('dragend', () => {
        if (!marker || !selectedGoogle.value) return;
        const pos = marker.getLatLng();
        selectedGoogle.value.latitude = pos.lat;
        selectedGoogle.value.longitude = pos.lng;
    });

    // Reset to fluid width and re-measure after layout settles
    setTimeout(() => {
        if (mapContainer.value) {
            mapContainer.value.style.width = '';
        }
        map?.invalidateSize();
        map?.setView([lat, lng], 16);
    }, 200);
}

function confirmLocation(): void {
    if (!selectedGoogle.value) return;
    editableName.value = selectedGoogle.value.name;
    showNameStep.value = true;
}

async function submitPlace(): Promise<void> {
    if (!selectedGoogle.value || !editableName.value.trim()) return;
    creatingPlace.value = true;

    selectedGoogle.value.name = editableName.value.trim();

    const res = await fetch('/api/places', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-XSRF-TOKEN': decodeURIComponent(
                document.cookie
                    .split('; ')
                    .find((c) => c.startsWith('XSRF-TOKEN='))
                    ?.split('=')[1] ?? '',
            ),
        },
        body: JSON.stringify(selectedGoogle.value),
    });

    const data = await res.json();
    creatingPlace.value = false;

    if (data.place) {
        emit('update:modelValue', data.place.id);
        emit('placed', { id: data.place.id });
        close();
    }
}

function backToMap(): void {
    showNameStep.value = false;
}

function open(): void {
    mode.value = 'search';
    internalQuery.value = '';
    internalResults.value = [];
}

function switchToGoogle(): void {
    mode.value = 'google';
    googleQuery.value = internalQuery.value;
    googleResults.value = [];
    showMap.value = false;
    showNameStep.value = false;
    selectedGoogle.value = null;
}

function close(): void {
    mode.value = 'idle';
    internalQuery.value = '';
    internalResults.value = [];
    googleQuery.value = '';
    googleResults.value = [];
    showMap.value = false;
    showNameStep.value = false;
    editableName.value = '';
    selectedGoogle.value = null;
    if (map) {
        map.remove();
        map = null;
    }
}

onBeforeUnmount(() => {
    if (map) {
        map.remove();
        map = null;
    }
});
</script>

<template>
    <div class="space-y-2">
        <!-- Idle state: show current place or add button -->
        <template v-if="mode === 'idle'">
            <div v-if="currentPlace" class="space-y-1">
                <p class="text-sm text-muted-foreground">
                    {{ currentPlace.name }}
                    <template v-if="currentPlace.city || currentPlace.region">
                        <br />
                        <span class="text-xs">
                            {{
                                [
                                    currentPlace.city,
                                    currentPlace.region,
                                    currentPlace.country,
                                ]
                                    .filter(Boolean)
                                    .join(', ')
                            }}
                        </span>
                    </template>
                </p>
                <button
                    class="text-xs text-muted-foreground hover:underline"
                    @click="open"
                >
                    Cambiar lugar
                </button>
            </div>
            <button
                v-else
                class="text-sm text-muted-foreground hover:underline"
                @click="open"
            >
                + Agregar ubicación
            </button>
        </template>

        <!-- Search mode: internal places -->
        <template v-if="mode === 'search'">
            <Input
                v-model="internalQuery"
                type="text"
                placeholder="Buscar lugar..."
                class="text-sm"
                autofocus
            />
            <div
                v-if="
                    internalResults.length > 0 ||
                    (internalQuery.length >= 2 && !internalLoading)
                "
                class="max-h-48 overflow-y-auto rounded-md border"
            >
                <button
                    v-for="place in internalResults"
                    :key="place.id"
                    class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm hover:bg-accent"
                    @click="selectExistingPlace(place)"
                >
                    <MapPin class="size-3 shrink-0 text-muted-foreground" />
                    <span>{{ place.name }}</span>
                    <span
                        v-if="place.city"
                        class="text-xs text-muted-foreground"
                    >
                        {{ place.city }}
                    </span>
                </button>
                <button
                    v-if="internalQuery.length >= 2 && !internalLoading"
                    class="flex w-full items-center gap-2 border-t px-3 py-2 text-left text-xs text-primary hover:bg-accent"
                    @click="switchToGoogle"
                >
                    <Search class="size-3 shrink-0" />
                    <span>
                        ¿No encuentras el lugar?
                        <span class="font-medium">Agrégalo</span>
                    </span>
                </button>
            </div>
            <button
                class="text-xs text-muted-foreground hover:underline"
                @click="close"
            >
                Cancelar
            </button>
        </template>

        <!-- Google mode: Google Places autocomplete + map -->
        <template v-if="mode === 'google'">
            <div v-if="!showMap" class="space-y-2">
                <Label>Buscar en Google Maps</Label>
                <div class="relative">
                    <Search
                        class="pointer-events-none absolute top-1/2 left-2.5 size-4 -translate-y-1/2 text-muted-foreground"
                    />
                    <Input
                        v-model="googleQuery"
                        type="text"
                        placeholder="Ej: Plaza de Armas, Santiago"
                        class="pl-9 text-sm"
                        autofocus
                    />
                </div>
                <div
                    v-if="googleResults.length > 0"
                    class="max-h-48 overflow-y-auto rounded-md border"
                >
                    <button
                        v-for="prediction in googleResults"
                        :key="prediction.place_id"
                        class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm hover:bg-accent"
                        @click="selectGooglePrediction(prediction)"
                    >
                        <MapPin class="size-3 shrink-0 text-muted-foreground" />
                        <div class="min-w-0">
                            <div class="truncate font-medium">
                                {{ prediction.structured_formatting.main_text }}
                            </div>
                            <div class="truncate text-xs text-muted-foreground">
                                {{
                                    prediction.structured_formatting
                                        .secondary_text
                                }}
                            </div>
                        </div>
                    </button>
                </div>
                <p
                    v-if="
                        googleQuery.length >= 2 &&
                        !googleLoading &&
                        googleResults.length === 0
                    "
                    class="text-xs text-muted-foreground"
                >
                    Sin resultados de Google.
                </p>
            </div>

            <!-- Step 1: Map confirmation -->
            <div
                v-if="showMap && selectedGoogle && !showNameStep"
                class="space-y-2"
            >
                <p class="text-sm font-medium">
                    {{ selectedGoogle.name }}
                </p>
                <p class="text-xs text-muted-foreground">
                    Arrastra el pin para ajustar la ubicación exacta.
                </p>
                <div
                    ref="mapContainer"
                    class="h-48 w-full overflow-hidden rounded-md border"
                />
                <div class="flex gap-2">
                    <Button size="sm" @click="confirmLocation">
                        Confirmar ubicación
                    </Button>
                    <Button size="sm" variant="ghost" @click="close">
                        Cancelar
                    </Button>
                </div>
            </div>

            <!-- Step 2: Name confirmation -->
            <div v-if="showNameStep && selectedGoogle" class="space-y-2">
                <Label>Nombre del lugar</Label>
                <p class="text-xs text-muted-foreground">
                    Puedes cambiar el nombre si el lugar se conocía de otra
                    forma.
                </p>
                <Input
                    v-model="editableName"
                    type="text"
                    placeholder="Ej: Banco Boston"
                    class="text-sm"
                    autofocus
                />
                <p
                    v-if="
                        selectedGoogle.city ||
                        selectedGoogle.region ||
                        selectedGoogle.country
                    "
                    class="text-xs text-muted-foreground"
                >
                    {{
                        [
                            selectedGoogle.city,
                            selectedGoogle.region,
                            selectedGoogle.country,
                        ]
                            .filter(Boolean)
                            .join(', ')
                    }}
                </p>
                <div class="flex gap-2">
                    <Button
                        size="sm"
                        :disabled="creatingPlace || !editableName.trim()"
                        @click="submitPlace"
                    >
                        Crear lugar
                    </Button>
                    <Button size="sm" variant="ghost" @click="backToMap">
                        Volver
                    </Button>
                </div>
            </div>

            <button
                v-if="!showMap && !showNameStep"
                class="text-xs text-muted-foreground hover:underline"
                @click="close"
            >
                Cancelar
            </button>
        </template>
    </div>
</template>
