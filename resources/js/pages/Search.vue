<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Search as SearchIcon, X } from 'lucide-vue-next';
import { ref } from 'vue';
import PhotoCard from '@/components/PhotoCard.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem, PaginatedData, Photo, Place, Tag } from '@/types';

type Props = {
    photos: PaginatedData<Photo>;
    filters: {
        q: string;
        place: string | null;
        decade: string | null;
        tag: string | null;
    };
    facets: {
        places: (Pick<Place, 'id' | 'name' | 'slug'> & { count: number })[];
        tags: (Pick<Tag, 'id' | 'name' | 'slug'> & { count: number })[];
    };
};

const props = defineProps<Props>();

const searchQuery = ref(props.filters.q || '');

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Buscar',
        href: '/search',
    },
];

const decades = Array.from({ length: 18 }, (_, i) => {
    const year = 1850 + i * 10;
    return { label: `${year}s`, value: String(year) };
});

function submitSearch(): void {
    applyFilters({ q: searchQuery.value.trim() });
}

function applyFilters(overrides: Record<string, string | null> = {}): void {
    const params: Record<string, string> = {};

    const q = overrides.q !== undefined ? overrides.q : props.filters.q;
    const place =
        overrides.place !== undefined ? overrides.place : props.filters.place;
    const decade =
        overrides.decade !== undefined
            ? overrides.decade
            : props.filters.decade;
    const tag = overrides.tag !== undefined ? overrides.tag : props.filters.tag;

    if (q) {
        params.q = q;
    }
    if (place) {
        params.place = place;
    }
    if (decade) {
        params.decade = decade;
    }
    if (tag) {
        params.tag = tag;
    }

    router.get('/search', params, { preserveState: true });
}

function clearFilter(key: string): void {
    applyFilters({ [key]: null });
}

function clearAllFilters(): void {
    router.get('/search');
}
</script>

<template>
    <Head title="Buscar">
        <meta
            head-key="description"
            name="description"
            content="Busca fotografías históricas de Chile por lugar, época, etiquetas y más."
        />
        <meta name="robots" content="noindex, follow" />
    </Head>

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4">
            <!-- Search Header -->
            <div class="space-y-4">
                <h1 class="text-2xl font-semibold tracking-tight">
                    Buscar fotos
                </h1>
                <form
                    class="flex w-full max-w-lg gap-2"
                    @submit.prevent="submitSearch"
                >
                    <div class="relative flex-1">
                        <SearchIcon
                            class="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                        />
                        <Input
                            v-model="searchQuery"
                            type="search"
                            placeholder="Buscar fotos, lugares, personas..."
                            class="pl-10"
                        />
                    </div>
                    <Button type="submit">Buscar</Button>
                </form>
            </div>

            <!-- Active Filters -->
            <div
                v-if="
                    props.filters.place ||
                    props.filters.decade ||
                    props.filters.tag
                "
                class="flex flex-wrap items-center gap-2"
            >
                <span class="text-sm text-muted-foreground">Filtros:</span>
                <Badge
                    v-if="props.filters.place"
                    variant="secondary"
                    class="cursor-pointer gap-1"
                    @click="clearFilter('place')"
                >
                    Lugar:
                    {{
                        props.facets.places.find(
                            (p) => p.slug === props.filters.place,
                        )?.name || props.filters.place
                    }}
                    <X class="size-3" />
                </Badge>
                <Badge
                    v-if="props.filters.decade"
                    variant="secondary"
                    class="cursor-pointer gap-1"
                    @click="clearFilter('decade')"
                >
                    Década: {{ props.filters.decade }}s
                    <X class="size-3" />
                </Badge>
                <Badge
                    v-if="props.filters.tag"
                    variant="secondary"
                    class="cursor-pointer gap-1"
                    @click="clearFilter('tag')"
                >
                    Tag:
                    {{
                        props.facets.tags.find(
                            (t) => t.slug === props.filters.tag,
                        )?.name || props.filters.tag
                    }}
                    <X class="size-3" />
                </Badge>
                <button
                    class="text-sm text-muted-foreground underline hover:text-foreground"
                    @click="clearAllFilters"
                >
                    Limpiar todo
                </button>
            </div>

            <div class="flex flex-col gap-6 lg:flex-row">
                <!-- Filter Sidebar -->
                <aside class="w-full shrink-0 space-y-6 lg:w-64">
                    <!-- Places filter -->
                    <div v-if="props.facets.places.length > 0">
                        <h3
                            class="mb-3 text-sm font-medium text-muted-foreground"
                        >
                            Lugar
                        </h3>
                        <div class="max-h-48 space-y-1 overflow-y-auto">
                            <button
                                v-for="place in props.facets.places"
                                :key="place.id"
                                class="flex w-full items-center justify-between rounded-md px-2 py-1.5 text-left text-sm transition-colors hover:bg-accent"
                                :class="{
                                    'bg-accent font-medium':
                                        props.filters.place === place.slug,
                                }"
                                @click="
                                    applyFilters({
                                        place:
                                            props.filters.place === place.slug
                                                ? null
                                                : place.slug,
                                    })
                                "
                            >
                                <span>{{ place.name }}</span>
                                <span class="text-xs text-muted-foreground">
                                    {{ place.count }}
                                </span>
                            </button>
                        </div>
                    </div>

                    <!-- Decades filter -->
                    <div>
                        <h3
                            class="mb-3 text-sm font-medium text-muted-foreground"
                        >
                            Década
                        </h3>
                        <div class="max-h-48 space-y-1 overflow-y-auto">
                            <button
                                v-for="d in decades"
                                :key="d.value"
                                class="block w-full rounded-md px-2 py-1.5 text-left text-sm transition-colors hover:bg-accent"
                                :class="{
                                    'bg-accent font-medium':
                                        props.filters.decade === d.value,
                                }"
                                @click="
                                    applyFilters({
                                        decade:
                                            props.filters.decade === d.value
                                                ? null
                                                : d.value,
                                    })
                                "
                            >
                                {{ d.label }}
                            </button>
                        </div>
                    </div>

                    <!-- Tags filter -->
                    <div v-if="props.facets.tags.length > 0">
                        <h3
                            class="mb-3 text-sm font-medium text-muted-foreground"
                        >
                            Etiquetas
                        </h3>
                        <div class="max-h-48 space-y-1 overflow-y-auto">
                            <button
                                v-for="tag in props.facets.tags"
                                :key="tag.id"
                                class="flex w-full items-center justify-between rounded-md px-2 py-1.5 text-left text-sm transition-colors hover:bg-accent"
                                :class="{
                                    'bg-accent font-medium':
                                        props.filters.tag === tag.slug,
                                }"
                                @click="
                                    applyFilters({
                                        tag:
                                            props.filters.tag === tag.slug
                                                ? null
                                                : tag.slug,
                                    })
                                "
                            >
                                <span>{{ tag.name }}</span>
                                <span class="text-xs text-muted-foreground">
                                    {{ tag.count }}
                                </span>
                            </button>
                        </div>
                    </div>
                </aside>

                <!-- Results -->
                <div class="flex-1">
                    <div v-if="props.photos.data.length > 0" class="space-y-6">
                        <p class="text-sm text-muted-foreground">
                            {{ props.photos.meta.total }}
                            {{
                                props.photos.meta.total === 1
                                    ? 'resultado'
                                    : 'resultados'
                            }}
                        </p>
                        <div
                            class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3"
                        >
                            <PhotoCard
                                v-for="photo in props.photos.data"
                                :key="photo.id"
                                :photo="photo"
                            />
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
                                Página
                                {{ props.photos.meta.current_page }}
                                de
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

                    <!-- Empty State -->
                    <div
                        v-else
                        class="flex flex-col items-center justify-center rounded-xl border border-dashed border-sidebar-border/70 p-12 dark:border-sidebar-border"
                    >
                        <SearchIcon
                            class="mb-4 size-12 text-muted-foreground"
                        />
                        <p class="text-lg font-medium">
                            No se encontraron resultados
                        </p>
                        <p class="mt-1 text-sm text-muted-foreground">
                            Intenta con otros términos de búsqueda o ajusta los
                            filtros.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
