<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import {
    Calendar,
    Camera,
    ImagePlus,
    MapPin,
    Tag as TagIcon,
    User,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { index as photosIndex, show as photosShow } from '@/routes/photos';
import { store as comparisonsStore } from '@/routes/photos/comparisons';
import type { BreadcrumbItem, ComparisonPhoto, Photo } from '@/types';

type Props = {
    photo: Photo;
};

const props = defineProps<Props>();

const page = usePage();
const auth = computed(() => page.props.auth);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Fotos',
        href: photosIndex(),
    },
    {
        title:
            props.photo.description.length > 40
                ? props.photo.description.substring(0, 40) + '...'
                : props.photo.description,
        href: photosShow(props.photo.id),
    },
];

const showComparisonForm = ref(false);

const comparisonForm = useForm<{
    photo: File | null;
    description: string;
    taken_at: string;
}>({
    photo: null,
    description: '',
    taken_at: '',
});

function onComparisonFileChange(event: Event): void {
    const target = event.target as HTMLInputElement;

    if (target.files && target.files.length > 0) {
        comparisonForm.photo = target.files[0];
    }
}

function submitComparison(): void {
    comparisonForm.post(comparisonsStore.url(props.photo.id), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            showComparisonForm.value = false;
            comparisonForm.reset();
        },
    });
}

function getDisplayImage(photo: Photo): string | null {
    const medium = photo.files.find((f) => f.variant === 'medium');
    const original = photo.files.find((f) => f.variant === 'original');

    return medium?.url ?? original?.url ?? null;
}

function getComparisonImage(comparison: ComparisonPhoto): string | null {
    return comparison.medium_url ?? comparison.original_url ?? null;
}

function formatDateRange(photo: Photo): string {
    switch (photo.date_precision) {
        case 'exact':
            return photo.year_to && photo.year_to !== photo.year_from
                ? `${photo.year_from}--${photo.year_to}`
                : `${photo.year_from}`;
        case 'year':
            return photo.year_to && photo.year_to !== photo.year_from
                ? `${photo.year_from}--${photo.year_to}`
                : `${photo.year_from}`;
        case 'decade':
            return `Decada de ${photo.year_from}`;
        case 'circa':
            return `~${photo.year_from}`;
        default:
            return `${photo.year_from}`;
    }
}

function formatPrecisionLabel(precision: Photo['date_precision']): string {
    switch (precision) {
        case 'exact':
            return 'Fecha exacta';
        case 'year':
            return 'Ano';
        case 'decade':
            return 'Decada';
        case 'circa':
            return 'Aproximada';
        default:
            return precision;
    }
}

const yearDisplay = computed(() => formatDateRange(props.photo));

const ogTitle = computed(() => {
    const desc = props.photo.description;

    return desc.length > 70 ? desc.substring(0, 67) + '...' : desc;
});

const ogDescription = computed(
    () => `${props.photo.description} (${yearDisplay.value})`,
);

const ogImage = computed(() => {
    const medium = props.photo.files.find((f) => f.variant === 'medium');
    const original = props.photo.files.find((f) => f.variant === 'original');

    return medium?.url ?? original?.url ?? null;
});

const hasComparisons = computed(
    () => props.photo.comparisons && props.photo.comparisons.length > 0,
);

const userHasComparison = computed(() => {
    if (!auth.value?.user || !props.photo.comparisons) {
        return false;
    }

    return props.photo.comparisons.some(
        (c) => c.user.id === auth.value.user.id,
    );
});
</script>

<template>
    <Head :title="`${ogTitle} — Archivo de Chile`">
        <meta property="og:title" :content="ogTitle" />
        <meta property="og:description" :content="ogDescription" />
        <meta v-if="ogImage" property="og:image" :content="ogImage" />
        <meta property="og:type" content="article" />
    </Head>

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4">
            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Image -->
                <div class="lg:col-span-2">
                    <div
                        class="overflow-hidden rounded-xl border border-sidebar-border/70 bg-muted dark:border-sidebar-border"
                    >
                        <img
                            v-if="getDisplayImage(props.photo)"
                            :src="getDisplayImage(props.photo)!"
                            :alt="props.photo.description"
                            class="w-full object-contain"
                        />
                        <div
                            v-else
                            class="flex aspect-video items-center justify-center text-muted-foreground"
                        >
                            <span class="text-sm">
                                Imagen en procesamiento...
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Metadata -->
                <div class="space-y-6">
                    <!-- Description -->
                    <div>
                        <h1 class="text-xl font-semibold tracking-tight">
                            {{ props.photo.description }}
                        </h1>
                    </div>

                    <!-- Date -->
                    <div class="space-y-1">
                        <div
                            class="flex items-center gap-2 text-sm font-medium"
                        >
                            <Calendar class="size-4 text-muted-foreground" />
                            Fecha
                        </div>
                        <p class="text-sm text-muted-foreground">
                            {{ formatDateRange(props.photo) }}
                            <span class="text-xs">
                                ({{
                                    formatPrecisionLabel(
                                        props.photo.date_precision,
                                    )
                                }})
                            </span>
                        </p>
                    </div>

                    <!-- Place -->
                    <div v-if="props.photo.place" class="space-y-1">
                        <div
                            class="flex items-center gap-2 text-sm font-medium"
                        >
                            <MapPin class="size-4 text-muted-foreground" />
                            Lugar
                        </div>
                        <p class="text-sm text-muted-foreground">
                            {{ props.photo.place.name }}
                            <template
                                v-if="
                                    props.photo.place.city ||
                                    props.photo.place.region
                                "
                            >
                                <br />
                                <span class="text-xs">
                                    {{
                                        [
                                            props.photo.place.city,
                                            props.photo.place.region,
                                            props.photo.place.country,
                                        ]
                                            .filter(Boolean)
                                            .join(', ')
                                    }}
                                </span>
                            </template>
                        </p>
                    </div>

                    <!-- Tags -->
                    <div v-if="props.photo.tags.length > 0" class="space-y-2">
                        <div
                            class="flex items-center gap-2 text-sm font-medium"
                        >
                            <TagIcon class="size-4 text-muted-foreground" />
                            Etiquetas
                        </div>
                        <div class="flex flex-wrap gap-1.5">
                            <Badge
                                v-for="tag in props.photo.tags"
                                :key="tag.id"
                                variant="secondary"
                            >
                                {{ tag.name }}
                            </Badge>
                        </div>
                    </div>

                    <!-- Source/Credit -->
                    <div v-if="props.photo.source_credit" class="space-y-1">
                        <div
                            class="flex items-center gap-2 text-sm font-medium"
                        >
                            <Camera class="size-4 text-muted-foreground" />
                            Fuente / Credito
                        </div>
                        <p class="text-sm text-muted-foreground">
                            {{ props.photo.source_credit }}
                        </p>
                    </div>

                    <!-- Uploader -->
                    <div class="space-y-1">
                        <div
                            class="flex items-center gap-2 text-sm font-medium"
                        >
                            <User class="size-4 text-muted-foreground" />
                            Subida por
                        </div>
                        <p class="text-sm text-muted-foreground">
                            {{ props.photo.user.name }}
                        </p>
                    </div>

                    <!-- Upload comparison button (auth only) -->
                    <div
                        v-if="
                            auth?.user &&
                            !userHasComparison &&
                            !showComparisonForm
                        "
                    >
                        <Button
                            variant="outline"
                            class="w-full"
                            @click="showComparisonForm = true"
                        >
                            <ImagePlus class="mr-2 size-4" />
                            Subir foto del ahora
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Comparison upload form -->
            <div
                v-if="showComparisonForm"
                class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
            >
                <h2 class="mb-4 text-lg font-semibold">Subir foto del ahora</h2>
                <form class="space-y-4" @submit.prevent="submitComparison">
                    <div class="space-y-2">
                        <Label for="comparison-photo">Foto</Label>
                        <Input
                            id="comparison-photo"
                            type="file"
                            accept="image/*"
                            @change="onComparisonFileChange"
                        />
                        <InputError :message="comparisonForm.errors.photo" />
                    </div>

                    <div class="space-y-2">
                        <Label for="comparison-description"
                            >Descripcion (opcional)</Label
                        >
                        <Input
                            id="comparison-description"
                            v-model="comparisonForm.description"
                            type="text"
                            placeholder="Describe la foto actual..."
                        />
                        <InputError
                            :message="comparisonForm.errors.description"
                        />
                    </div>

                    <div class="space-y-2">
                        <Label for="comparison-taken-at"
                            >Fecha de la foto (opcional)</Label
                        >
                        <Input
                            id="comparison-taken-at"
                            v-model="comparisonForm.taken_at"
                            type="date"
                        />
                        <InputError :message="comparisonForm.errors.taken_at" />
                    </div>

                    <div class="flex gap-2">
                        <Button
                            type="submit"
                            :disabled="comparisonForm.processing"
                        >
                            Subir
                        </Button>
                        <Button
                            type="button"
                            variant="ghost"
                            @click="
                                showComparisonForm = false;
                                comparisonForm.reset();
                            "
                        >
                            Cancelar
                        </Button>
                    </div>
                </form>
            </div>

            <!-- Comparisons section (side by side) -->
            <div v-if="hasComparisons" class="space-y-4">
                <h2 class="text-lg font-semibold">Foto del ahora</h2>

                <div
                    v-for="comparison in props.photo.comparisons"
                    :key="comparison.id"
                    class="overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
                >
                    <div class="grid gap-0 md:grid-cols-2">
                        <!-- Historical photo -->
                        <div class="relative">
                            <div class="absolute top-2 left-2 z-10">
                                <Badge variant="secondary"> Antes </Badge>
                            </div>
                            <img
                                v-if="getDisplayImage(props.photo)"
                                :src="getDisplayImage(props.photo)!"
                                :alt="props.photo.description"
                                class="h-full w-full object-cover"
                            />
                        </div>

                        <!-- Comparison photo -->
                        <div class="relative">
                            <div class="absolute top-2 left-2 z-10">
                                <Badge>Ahora</Badge>
                            </div>
                            <img
                                v-if="getComparisonImage(comparison)"
                                :src="getComparisonImage(comparison)!"
                                :alt="
                                    comparison.description ?? 'Foto del ahora'
                                "
                                class="h-full w-full object-cover"
                            />
                            <div
                                v-else
                                class="flex aspect-video items-center justify-center bg-muted text-muted-foreground"
                            >
                                <span class="text-sm">
                                    Imagen en procesamiento...
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Comparison metadata -->
                    <div
                        class="flex items-center gap-4 border-t border-sidebar-border/70 px-4 py-3 text-sm text-muted-foreground dark:border-sidebar-border"
                    >
                        <span> Por {{ comparison.user.name }} </span>
                        <span v-if="comparison.taken_at">
                            {{ comparison.taken_at }}
                        </span>
                        <span v-if="comparison.description">
                            {{ comparison.description }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
