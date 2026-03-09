<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { MapPin } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { index as placesIndex, show as placesShow } from '@/routes/places';
import type { BreadcrumbItem, PaginatedData, Place } from '@/types';

type Props = {
    places: PaginatedData<Place>;
};

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Lugares',
        href: placesIndex(),
    },
];
</script>

<template>
    <Head title="Lugares">
        <meta
            head-key="description"
            name="description"
            content="Descubre lugares históricos de Chile a través de fotografías de archivo. Ciudades, pueblos y rincones del patrimonio chileno."
        />
        <meta property="og:title" content="Lugares — Archivo de Chile" />
        <meta
            property="og:description"
            content="Descubre lugares históricos de Chile a través de fotografías de archivo."
        />
    </Head>

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4">
            <h1 class="text-2xl font-semibold tracking-tight">Lugares</h1>

            <div
                v-if="props.places.data.length > 0"
                class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3"
            >
                <Link
                    v-for="place in props.places.data"
                    :key="place.id"
                    :href="placesShow(place.slug)"
                    class="group overflow-hidden rounded-xl border border-sidebar-border/70 bg-card shadow-sm transition-shadow hover:shadow-md dark:border-sidebar-border"
                >
                    <!-- Photo collage -->
                    <div
                        v-if="
                            place.preview_photos &&
                            place.preview_photos.length > 0
                        "
                        class="relative aspect-[4/3] overflow-hidden bg-muted"
                    >
                        <!-- 1 photo: full -->
                        <img
                            v-if="place.preview_photos.length === 1"
                            :src="place.preview_photos[0].url!"
                            :alt="place.name"
                            class="size-full object-cover transition-transform duration-300 group-hover:scale-105"
                        />
                        <!-- 2 photos: side by side -->
                        <div
                            v-else-if="place.preview_photos.length === 2"
                            class="grid h-full grid-cols-2 gap-0.5"
                        >
                            <img
                                v-for="p in place.preview_photos"
                                :key="p.id"
                                :src="p.url!"
                                :alt="place.name"
                                class="size-full object-cover"
                            />
                        </div>
                        <!-- 3 photos: one large left, two stacked right -->
                        <div
                            v-else-if="place.preview_photos.length === 3"
                            class="grid h-full grid-cols-2 gap-0.5"
                        >
                            <img
                                :src="place.preview_photos[0].url!"
                                :alt="place.name"
                                class="size-full object-cover"
                            />
                            <div class="grid grid-rows-2 gap-0.5">
                                <img
                                    :src="place.preview_photos[1].url!"
                                    :alt="place.name"
                                    class="size-full object-cover"
                                />
                                <img
                                    :src="place.preview_photos[2].url!"
                                    :alt="place.name"
                                    class="size-full object-cover"
                                />
                            </div>
                        </div>
                        <!-- 4 photos: 2x2 grid -->
                        <div
                            v-else
                            class="grid h-full grid-cols-2 grid-rows-2 gap-0.5"
                        >
                            <img
                                v-for="p in place.preview_photos.slice(0, 4)"
                                :key="p.id"
                                :src="p.url!"
                                :alt="place.name"
                                class="size-full object-cover"
                            />
                        </div>
                    </div>
                    <!-- No photos placeholder -->
                    <div
                        v-else
                        class="flex aspect-[4/3] items-center justify-center bg-muted"
                    >
                        <MapPin class="size-8 text-muted-foreground/40" />
                    </div>

                    <div class="space-y-1 p-4">
                        <h2 class="text-sm font-medium">
                            {{ place.name }}
                        </h2>
                        <p
                            v-if="place.region || place.city"
                            class="text-xs text-muted-foreground"
                        >
                            {{
                                [place.city, place.region]
                                    .filter(Boolean)
                                    .join(', ')
                            }}
                        </p>
                        <p class="text-xs text-muted-foreground">
                            {{ place.photos_count ?? 0 }}
                            {{
                                (place.photos_count ?? 0) === 1
                                    ? 'foto'
                                    : 'fotos'
                            }}
                        </p>
                    </div>
                </Link>
            </div>

            <div
                v-else
                class="flex flex-1 flex-col items-center justify-center rounded-xl border border-dashed border-sidebar-border/70 p-12 dark:border-sidebar-border"
            >
                <p class="text-sm text-muted-foreground">
                    No hay lugares registrados todavia.
                </p>
            </div>

            <!-- Págination -->
            <nav
                v-if="props.places.meta.last_page > 1"
                class="flex items-center justify-center gap-2"
            >
                <Link
                    v-if="props.places.links.prev"
                    :href="props.places.links.prev"
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
                    Página {{ props.places.meta.current_page }} de
                    {{ props.places.meta.last_page }}
                </span>

                <Link
                    v-if="props.places.links.next"
                    :href="props.places.links.next"
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
