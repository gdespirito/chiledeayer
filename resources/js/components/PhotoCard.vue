<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { MapPin } from 'lucide-vue-next';
import { show as photosShow } from '@/routes/photos';
import type { Photo } from '@/types';

type Props = {
    photo: Photo;
};

const props = defineProps<Props>();

function getThumbnail(photo: Photo): string | null {
    const thumb = photo.files.find((f) => f.variant === 'thumb');
    const medium = photo.files.find((f) => f.variant === 'medium');
    const original = photo.files.find((f) => f.variant === 'original');

    return thumb?.url ?? medium?.url ?? original?.url ?? null;
}

function formatDateRange(photo: Photo): string {
    switch (photo.date_precision) {
        case 'exact':
        case 'year':
            return photo.year_to && photo.year_to !== photo.year_from
                ? `${photo.year_from}--${photo.year_to}`
                : `${photo.year_from}`;
        case 'decade':
            return `Década de ${photo.year_from}`;
        case 'circa':
            return `~${photo.year_from}`;
        default:
            return `${photo.year_from}`;
    }
}
</script>

<template>
    <Link
        :href="photosShow(props.photo.id)"
        class="group overflow-hidden rounded-xl border border-sidebar-border/70 bg-card shadow-sm transition-shadow hover:shadow-md dark:border-sidebar-border"
    >
        <div class="relative aspect-[4/3] overflow-hidden bg-muted">
            <img
                v-if="getThumbnail(props.photo)"
                :src="getThumbnail(props.photo)!"
                :alt="props.photo.description"
                class="size-full object-cover transition-transform duration-300 group-hover:scale-105"
            />
            <div
                v-else
                class="flex size-full items-center justify-center text-muted-foreground"
            >
                <span class="text-sm">Sin imagen</span>
            </div>
        </div>
        <div class="space-y-1.5 p-4">
            <p class="line-clamp-2 text-sm font-medium">
                {{ props.photo.description }}
            </p>
            <div class="flex items-center gap-3 text-xs text-muted-foreground">
                <span>{{ formatDateRange(props.photo) }}</span>
                <span v-if="props.photo.place" class="flex items-center gap-1">
                    <MapPin class="size-3" />
                    {{ props.photo.place.name }}
                </span>
            </div>
        </div>
    </Link>
</template>
