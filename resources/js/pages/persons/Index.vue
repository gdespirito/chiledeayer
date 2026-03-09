<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import JsonLd from '@/components/JsonLd.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { index as personsIndex, show as personsShow } from '@/routes/persons';
import type { BreadcrumbItem, PaginatedData, Person } from '@/types';

type Props = {
    persons: PaginatedData<Person>;
};

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Personas',
        href: personsIndex(),
    },
];
</script>

<template>
    <Head title="Personas">
        <meta
            head-key="description"
            name="description"
            content="Personas identificadas en fotografías históricas de Chile. Figuras históricas y ciudadanos del patrimonio visual chileno."
        />
        <meta property="og:title" content="Personas — Chile de Ayer" />
        <meta
            property="og:description"
            content="Personas identificadas en fotografías históricas de Chile."
        />
    </Head>
    <JsonLd
        :schema="{
            '@context': 'https://schema.org',
            '@type': 'CollectionPage',
            name: 'Personas — Chile de Ayer',
            description:
                'Personas identificadas en fotografías históricas de Chile. Figuras históricas y ciudadanos del patrimonio visual chileno.',
            url: 'https://chiledeayer.cl/persons',
            isPartOf: {
                '@type': 'WebSite',
                name: 'Chile de Ayer',
                url: 'https://chiledeayer.cl',
            },
        }"
    />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4">
            <h1 class="text-2xl font-semibold tracking-tight">Personas</h1>

            <div
                v-if="props.persons.data.length > 0"
                class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3"
            >
                <Link
                    v-for="person in props.persons.data"
                    :key="person.id"
                    :href="personsShow(person.id)"
                    class="group overflow-hidden rounded-xl border border-sidebar-border/70 bg-card p-4 shadow-sm transition-shadow hover:shadow-md dark:border-sidebar-border"
                >
                    <div class="space-y-1.5">
                        <div class="flex items-center gap-2">
                            <h2 class="text-sm font-medium">
                                {{ person.name }}
                            </h2>
                            <span
                                v-if="person.type === 'public'"
                                class="inline-flex items-center rounded-full bg-primary/10 px-2 py-0.5 text-xs font-medium text-primary"
                            >
                                Pública
                            </span>
                        </div>
                        <p
                            v-if="person.bio"
                            class="line-clamp-2 text-xs text-muted-foreground"
                        >
                            {{ person.bio }}
                        </p>
                        <p class="text-xs text-muted-foreground">
                            {{ person.photos_count ?? 0 }}
                            {{
                                (person.photos_count ?? 0) === 1
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
                    No hay personas registradas todavía.
                </p>
            </div>

            <!-- Pagination -->
            <nav
                v-if="props.persons.meta.last_page > 1"
                class="flex items-center justify-center gap-2"
            >
                <Link
                    v-if="props.persons.links.prev"
                    :href="props.persons.links.prev"
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
                    Página {{ props.persons.meta.current_page }} de
                    {{ props.persons.meta.last_page }}
                </span>

                <Link
                    v-if="props.persons.links.next"
                    :href="props.persons.links.next"
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
