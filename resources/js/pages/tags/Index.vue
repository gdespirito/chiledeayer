<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { TagIcon } from 'lucide-vue-next';
import JsonLd from '@/components/JsonLd.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { index as tagsIndex, show as tagsShow } from '@/routes/tags';
import type { BreadcrumbItem, PaginatedData, Tag } from '@/types';

type Props = {
    tags: PaginatedData<Tag>;
};

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Etiquetas',
        href: tagsIndex(),
    },
];
</script>

<template>
    <Head title="Etiquetas">
        <meta
            head-key="description"
            name="description"
            content="Explora las etiquetas del archivo fotográfico de Chile. Temas, épocas y categorías de fotografías históricas."
        />
        <meta property="og:title" content="Etiquetas — Chile de Ayer" />
        <meta
            property="og:description"
            content="Explora las etiquetas del archivo fotográfico de Chile."
        />
    </Head>
    <JsonLd
        :schema="{
            '@context': 'https://schema.org',
            '@type': 'CollectionPage',
            name: 'Etiquetas — Chile de Ayer',
            description:
                'Etiquetas del archivo fotográfico de Chile. Temas, épocas y categorías de fotografías históricas.',
            url: 'https://chiledeayer.cl/tags',
            isPartOf: {
                '@type': 'WebSite',
                name: 'Chile de Ayer',
                url: 'https://chiledeayer.cl',
            },
        }"
    />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4">
            <h1 class="text-2xl font-semibold tracking-tight">Etiquetas</h1>

            <div
                v-if="props.tags.data.length > 0"
                class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3"
            >
                <Link
                    v-for="tag in props.tags.data"
                    :key="tag.id"
                    :href="tagsShow(tag.slug)"
                    class="group overflow-hidden rounded-xl border border-sidebar-border/70 bg-card p-4 shadow-sm transition-shadow hover:shadow-md dark:border-sidebar-border"
                >
                    <div class="space-y-1.5">
                        <div class="flex items-center gap-2">
                            <TagIcon class="size-4 text-muted-foreground" />
                            <h2 class="text-sm font-medium">
                                {{ tag.name }}
                            </h2>
                        </div>
                        <p class="text-xs text-muted-foreground">
                            {{ tag.photos_count ?? 0 }}
                            {{
                                (tag.photos_count ?? 0) === 1 ? 'foto' : 'fotos'
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
                    No hay etiquetas registradas todavia.
                </p>
            </div>

            <!-- Págination -->
            <nav
                v-if="props.tags.meta.last_page > 1"
                class="flex items-center justify-center gap-2"
            >
                <Link
                    v-if="props.tags.links.prev"
                    :href="props.tags.links.prev"
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
                    Página {{ props.tags.meta.current_page }} de
                    {{ props.tags.meta.last_page }}
                </span>

                <Link
                    v-if="props.tags.links.next"
                    :href="props.tags.links.next"
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
