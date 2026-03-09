<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { MapPin } from 'lucide-vue-next';
import { computed } from 'vue';
import JsonLd from '@/components/JsonLd.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { index as personsIndex, show as personsShow } from '@/routes/persons';
import { show as photosShow } from '@/routes/photos';
import type { BreadcrumbItem, PaginatedData, Person, Photo } from '@/types';

type Props = {
    person: { data: Person };
    photos: PaginatedData<Photo>;
};

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Personas',
        href: personsIndex(),
    },
    {
        title: props.person.data.name,
        href: personsShow(props.person.data.id),
    },
];

const personSchema = computed(() => {
    const schema: Record<string, unknown> = {
        '@context': 'https://schema.org',
        '@type': 'Person',
        name: props.person.data.name,
        description: `Fotografías históricas de ${props.person.data.name} en el Archivo de Chile.`,
    };
    if (props.person.data.bio) {
        schema.description = props.person.data.bio;
    }
    return schema;
});

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
    <Head :title="props.person.data.name">
        <meta
            head-key="description"
            name="description"
            :content="`Fotografías históricas de ${props.person.data.name} en el Archivo de Chile.`"
        />
        <meta
            property="og:title"
            :content="`${props.person.data.name} — Archivo de Chile`"
        />
        <meta
            property="og:description"
            :content="`Fotografías históricas de ${props.person.data.name}.`"
        />
    </Head>
    <JsonLd :schema="personSchema" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4">
            <div class="space-y-2">
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-semibold tracking-tight">
                        {{ props.person.data.name }}
                    </h1>
                    <span
                        v-if="props.person.data.type === 'public'"
                        class="inline-flex items-center rounded-full bg-primary/10 px-2.5 py-1 text-xs font-medium text-primary"
                    >
                        Pública
                    </span>
                </div>
                <p
                    v-if="props.person.data.bio"
                    class="max-w-2xl text-sm text-muted-foreground"
                >
                    {{ props.person.data.bio }}
                </p>
                <p class="text-sm text-muted-foreground">
                    {{ props.person.data.photos_count ?? 0 }}
                    {{
                        (props.person.data.photos_count ?? 0) === 1
                            ? 'foto'
                            : 'fotos'
                    }}
                </p>
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
                    Esta persona no aparece en ninguna foto todavía.
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
