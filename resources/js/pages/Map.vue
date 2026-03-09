<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { useLeafletIcons } from '@/composables/useLeafletIcons';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type MapPhoto = {
    id: number;
    lat: number;
    lng: number;
    title: string;
    year_from: number;
    thumb_url: string | null;
};

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Mapa', href: '/map' }];

const mapContainer = ref<HTMLDivElement | null>(null);
let map: L.Map | null = null;
let markerGroup: L.LayerGroup | null = null;
let debounceTimer: ReturnType<typeof setTimeout> | null = null;

const yearFrom = ref(1850);
const yearTo = ref(2026);
const loading = ref(false);
const photoCount = ref(0);

useLeafletIcons();

async function fetchPhotos(): Promise<void> {
    if (!map) return;

    const bounds = map.getBounds();
    const params = new URLSearchParams({
        north: bounds.getNorth().toString(),
        south: bounds.getSouth().toString(),
        east: bounds.getEast().toString(),
        west: bounds.getWest().toString(),
        year_from: yearFrom.value.toString(),
        year_to: yearTo.value.toString(),
    });

    loading.value = true;

    try {
        const response = await fetch(`/api/map/photos?${params}`);
        const photos: MapPhoto[] = await response.json();

        photoCount.value = photos.length;

        if (markerGroup) {
            markerGroup.clearLayers();
        }

        for (const photo of photos) {
            const desc =
                photo.title.length > 80
                    ? photo.title.substring(0, 80) + '...'
                    : photo.title;

            const thumbHtml = photo.thumb_url
                ? `<img src="${photo.thumb_url}" alt="" class="mb-2 h-24 w-full rounded object-cover" />`
                : '';

            const popupContent = `
                <div class="w-48">
                    ${thumbHtml}
                    <p class="text-sm font-medium leading-tight">${desc}</p>
                    <p class="mt-1 text-xs text-gray-500">${photo.year_from}</p>
                    <a href="/photos/${photo.id}" class="mt-2 inline-block text-xs font-medium text-blue-600 hover:underline">Ver foto</a>
                </div>
            `;

            const marker = L.marker([photo.lat, photo.lng]);
            marker.bindPopup(popupContent, { maxWidth: 220 });
            markerGroup?.addLayer(marker);
        }
    } catch {
        // Silently fail on network errors
    } finally {
        loading.value = false;
    }
}

function debouncedFetch(): void {
    if (debounceTimer) {
        clearTimeout(debounceTimer);
    }
    debounceTimer = setTimeout(() => fetchPhotos(), 300);
}

onMounted(() => {
    if (!mapContainer.value) return;

    map = L.map(mapContainer.value).setView([-33.45, -70.65], 6);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution:
            '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 19,
    }).addTo(map);

    markerGroup = L.layerGroup().addTo(map);

    map.on('moveend', debouncedFetch);

    fetchPhotos();
});

onBeforeUnmount(() => {
    if (debounceTimer) {
        clearTimeout(debounceTimer);
    }
    if (map) {
        map.remove();
        map = null;
    }
});

watch([yearFrom, yearTo], () => {
    debouncedFetch();
});
</script>

<template>
    <Head title="Mapa">
        <meta
            head-key="description"
            name="description"
            content="Mapa interactivo de fotografías históricas de Chile. Explora fotos por ubicación geográfica y rango temporal."
        />
        <meta property="og:title" content="Mapa — Chile de Ayer" />
        <meta
            property="og:description"
            content="Mapa interactivo de fotografías históricas de Chile."
        />
    </Head>

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="relative flex flex-1 flex-col">
            <!-- Map container -->
            <div ref="mapContainer" class="z-0 flex-1" />

            <!-- Temporal slider -->
            <div
                class="absolute bottom-6 left-1/2 z-[1000] w-full max-w-lg -translate-x-1/2 px-4"
            >
                <div
                    class="rounded-xl border border-sidebar-border/70 bg-background/95 p-4 shadow-lg backdrop-blur-sm dark:border-sidebar-border"
                >
                    <div class="mb-2 flex items-center justify-between text-xs">
                        <span class="font-medium text-muted-foreground">
                            Rango temporal
                        </span>
                        <span v-if="loading" class="text-muted-foreground">
                            Cargando...
                        </span>
                        <span v-else class="text-muted-foreground">
                            {{ photoCount }}
                            {{ photoCount === 1 ? 'foto' : 'fotos' }}
                        </span>
                    </div>

                    <div class="space-y-3">
                        <div class="flex items-center gap-3">
                            <label
                                for="year-from"
                                class="w-12 text-right text-xs font-medium text-foreground"
                            >
                                {{ yearFrom }}
                            </label>
                            <input
                                id="year-from"
                                v-model.number="yearFrom"
                                type="range"
                                min="1850"
                                max="2026"
                                class="h-1.5 flex-1 cursor-pointer appearance-none rounded-lg bg-muted accent-primary"
                            />
                        </div>

                        <div class="flex items-center gap-3">
                            <label
                                for="year-to"
                                class="w-12 text-right text-xs font-medium text-foreground"
                            >
                                {{ yearTo }}
                            </label>
                            <input
                                id="year-to"
                                v-model.number="yearTo"
                                type="range"
                                min="1850"
                                max="2026"
                                class="h-1.5 flex-1 cursor-pointer appearance-none rounded-lg bg-muted accent-primary"
                            />
                        </div>

                        <div
                            class="flex justify-between text-[10px] text-muted-foreground"
                        >
                            <span>1850</span>
                            <span>2026</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
:deep(.leaflet-container) {
    font-family: inherit;
}
</style>
