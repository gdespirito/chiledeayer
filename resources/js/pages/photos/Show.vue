<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import {
    Calendar,
    Camera,
    MapPin,
    Tag as TagIcon,
    User,
} from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import AppLayout from '@/layouts/AppLayout.vue';
import { index as photosIndex, show as photosShow } from '@/routes/photos';
import type { BreadcrumbItem, Photo } from '@/types';

type Props = {
    photo: Photo;
};

const props = defineProps<Props>();

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

function getDisplayImage(photo: Photo): string | null {
    const medium = photo.files.find((f) => f.variant === 'medium');
    const original = photo.files.find((f) => f.variant === 'original');

    return medium?.url ?? original?.url ?? null;
}

function formatDateRange(photo: Photo): string {
    switch (photo.date_precision) {
        case 'exact':
            return photo.year_to && photo.year_to !== photo.year_from
                ? `${photo.year_from}–${photo.year_to}`
                : `${photo.year_from}`;
        case 'year':
            return photo.year_to && photo.year_to !== photo.year_from
                ? `${photo.year_from}–${photo.year_to}`
                : `${photo.year_from}`;
        case 'decade':
            return `Década de ${photo.year_from}`;
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
            return 'Año';
        case 'decade':
            return 'Década';
        case 'circa':
            return 'Aproximada';
        default:
            return precision;
    }
}
</script>

<template>
    <Head :title="props.photo.description" />

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
                            Fuente / Crédito
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
                </div>
            </div>
        </div>
    </AppLayout>
</template>
