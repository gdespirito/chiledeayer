<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { MapPin } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    create as photosCreate,
    index as photosIndex,
    show as photosShow,
} from '@/routes/photos';
import type { BreadcrumbItem, PaginatedData, Photo } from '@/types';

type Props = {
    photos: PaginatedData<Photo>;
};

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Fotos',
        href: photosIndex(),
    },
];

function getThumbnail(photo: Photo): string | null {
    const thumb = photo.files.find((f) => f.variant === 'thumb');
    const medium = photo.files.find((f) => f.variant === 'medium');
    const original = photo.files.find((f) => f.variant === 'original');

    return thumb?.url ?? medium?.url ?? original?.url ?? null;
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
</script>

<template>
    <Head title="Fotos">
        <meta
            head-key="description"
            name="description"
            content="Explora la colección de fotografías históricas de Chile. Imágenes de archivo que capturan la historia y cultura chilena."
        />
        <meta property="og:title" content="Fotos — Archivo de Chile" />
        <meta
            property="og:description"
            content="Explora la colección de fotografías históricas de Chile."
        />
    </Head>

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold tracking-tight">Fotos</h1>
                <Link
                    v-if="$page.props.auth.user"
                    :href="photosCreate()"
                    class="inline-flex h-9 items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow-xs transition-all hover:bg-primary/90"
                >
                    Subir foto
                </Link>
            </div>

            <div
                v-if="props.photos.data.length > 0"
                class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3"
            >
                <Link
                    v-for="photo in props.photos.data"
                    :key="photo.id"
                    :href="photosShow(photo.id)"
                    class="group overflow-hidden rounded-xl border border-sidebar-border/70 bg-card shadow-sm transition-shadow hover:shadow-md dark:border-sidebar-border"
                >
                    <div class="relative aspect-[4/3] overflow-hidden bg-muted">
                        <img
                            v-if="getThumbnail(photo)"
                            :src="getThumbnail(photo)!"
                            :alt="photo.description"
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
                            {{ photo.description }}
                        </p>
                        <div
                            class="flex items-center gap-3 text-xs text-muted-foreground"
                        >
                            <span>{{ formatDateRange(photo) }}</span>
                            <span
                                v-if="photo.place"
                                class="flex items-center gap-1"
                            >
                                <MapPin class="size-3" />
                                {{ photo.place.name }}
                            </span>
                        </div>
                    </div>
                </Link>
            </div>

            <div
                v-else
                class="flex flex-1 flex-col items-center justify-center rounded-xl border border-dashed border-sidebar-border/70 p-12 dark:border-sidebar-border"
            >
                <p class="text-sm text-muted-foreground">
                    No hay fotos todavía.
                </p>
            </div>

            <!-- Pagination -->
            <nav
                v-if="props.photos.meta.last_page > 1"
                class="flex items-center justify-center gap-2"
            >
                <Link
                    v-if="props.photos.links.prev"
                    :href="props.photos.links.prev"
                    class="inline-flex h-9 items-center justify-center rounded-md border bg-background px-4 text-sm font-medium shadow-xs transition-all hover:bg-accent hover:text-accent-foreground dark:border-input dark:bg-input/30 dark:hover:bg-input/50"
                >
                    Anterior
                </Link>
                <span
                    v-else
                    class="inline-flex h-9 items-center justify-center rounded-md border bg-background px-4 text-sm font-medium opacity-50 shadow-xs dark:border-input dark:bg-input/30"
                >
                    Anterior
                </span>

                <span class="text-sm text-muted-foreground">
                    Página {{ props.photos.meta.current_page }} de
                    {{ props.photos.meta.last_page }}
                </span>

                <Link
                    v-if="props.photos.links.next"
                    :href="props.photos.links.next"
                    class="inline-flex h-9 items-center justify-center rounded-md border bg-background px-4 text-sm font-medium shadow-xs transition-all hover:bg-accent hover:text-accent-foreground dark:border-input dark:bg-input/30 dark:hover:bg-input/50"
                >
                    Siguiente
                </Link>
                <span
                    v-else
                    class="inline-flex h-9 items-center justify-center rounded-md border bg-background px-4 text-sm font-medium opacity-50 shadow-xs dark:border-input dark:bg-input/30"
                >
                    Siguiente
                </span>
            </nav>
        </div>
    </AppLayout>
</template>
