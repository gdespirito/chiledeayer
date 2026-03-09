<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Trophy } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { leaderboard } from '@/routes';
import type { BreadcrumbItem, LeaderboardEntry, PaginatedData } from '@/types';

type Props = {
    users: PaginatedData<LeaderboardEntry>;
};

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Ranking',
        href: leaderboard(),
    },
];
</script>

<template>
    <Head title="Ranking">
        <meta
            head-key="description"
            name="description"
            content="Tabla de honor de los mayores contribuyentes del Archivo de Chile. Ranking por puntos e insignias."
        />
        <meta property="og:title" content="Ranking — Archivo de Chile" />
        <meta
            property="og:description"
            content="Tabla de honor de los mayores contribuyentes del Archivo de Chile."
        />
    </Head>

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4">
            <div class="flex items-center gap-2">
                <Trophy class="size-5 text-muted-foreground" />
                <h1 class="text-2xl font-semibold tracking-tight">Ranking</h1>
            </div>

            <div
                v-if="props.users.data.length > 0"
                class="overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
            >
                <table class="w-full text-sm">
                    <thead>
                        <tr
                            class="border-b border-sidebar-border/70 bg-muted/50 dark:border-sidebar-border"
                        >
                            <th
                                class="px-4 py-3 text-left font-medium text-muted-foreground"
                            >
                                #
                            </th>
                            <th
                                class="px-4 py-3 text-left font-medium text-muted-foreground"
                            >
                                Usuario
                            </th>
                            <th
                                class="px-4 py-3 text-left font-medium text-muted-foreground"
                            >
                                Nivel
                            </th>
                            <th
                                class="px-4 py-3 text-right font-medium text-muted-foreground"
                            >
                                Puntos
                            </th>
                            <th
                                class="px-4 py-3 text-right font-medium text-muted-foreground"
                            >
                                Insignias
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="(entry, idx) in props.users.data"
                            :key="entry.user.id"
                            class="border-b border-sidebar-border/70 last:border-b-0 dark:border-sidebar-border"
                        >
                            <td
                                class="px-4 py-3 font-medium text-muted-foreground"
                            >
                                {{
                                    (props.users.meta.current_page - 1) *
                                        props.users.meta.per_page +
                                    idx +
                                    1
                                }}
                            </td>
                            <td class="px-4 py-3 font-medium">
                                {{ entry.user.name }}
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                <span v-if="entry.level">
                                    {{ entry.level.icon }}
                                    {{ entry.level.name }}
                                </span>
                                <span v-else>--</span>
                            </td>
                            <td class="px-4 py-3 text-right tabular-nums">
                                {{ entry.total_points.toLocaleString() }}
                            </td>
                            <td class="px-4 py-3 text-right tabular-nums">
                                {{ entry.badges_count }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div
                v-else
                class="flex flex-1 flex-col items-center justify-center rounded-xl border border-dashed border-sidebar-border/70 p-12 dark:border-sidebar-border"
            >
                <p class="text-sm text-muted-foreground">
                    No hay usuarios en el ranking todavia.
                </p>
            </div>

            <!-- Págination -->
            <nav
                v-if="props.users.meta.last_page > 1"
                class="flex items-center justify-center gap-2"
            >
                <Link
                    v-if="props.users.links.prev"
                    :href="props.users.links.prev"
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
                    Página {{ props.users.meta.current_page }} de
                    {{ props.users.meta.last_page }}
                </span>

                <Link
                    v-if="props.users.links.next"
                    :href="props.users.links.next"
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
