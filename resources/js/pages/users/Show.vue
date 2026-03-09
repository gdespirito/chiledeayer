<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    Award,
    Calendar,
    MapPin,
    Star,
    User as UserIcon,
} from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { show as photosShow } from '@/routes/photos';
import type { BreadcrumbItem, Level, PaginatedData, Photo } from '@/types';

type Badge = {
    id: number;
    key: string;
    name: string;
    description: string;
};

type UserProfile = {
    id: number;
    name: string;
    created_at: string;
    total_points: number;
    level: Level | null;
    badges: Badge[];
    photos_count: number;
};

type Props = {
    user: { data: UserProfile };
    photos: PaginatedData<Photo>;
};

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: props.user.data.name,
        href: `/users/${props.user.data.id}`,
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

function formatMemberSince(dateString: string): string {
    const date = new Date(dateString);

    return date.toLocaleDateString('es-CL', {
        year: 'numeric',
        month: 'long',
    });
}
</script>

<template>
    <Head :title="props.user.data.name">
        <meta
            head-key="description"
            name="description"
            :content="`Perfil de ${props.user.data.name} en el Chile de Ayer. ${props.user.data.photos_count} fotos contribuidas.`"
        />
        <meta
            property="og:title"
            :content="`${props.user.data.name} — Chile de Ayer`"
        />
        <meta
            property="og:description"
            :content="`Perfil de ${props.user.data.name}. ${props.user.data.photos_count} fotos contribuidas.`"
        />
    </Head>

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4">
            <!-- Profile Header -->
            <div
                class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
            >
                <div class="flex items-start gap-4">
                    <div
                        class="flex size-16 items-center justify-center rounded-full bg-primary/10"
                    >
                        <UserIcon class="size-8 text-primary" />
                    </div>
                    <div class="flex-1 space-y-2">
                        <div class="flex items-center gap-3">
                            <h1 class="text-2xl font-semibold tracking-tight">
                                {{ props.user.data.name }}
                            </h1>
                            <span
                                v-if="props.user.data.level"
                                class="inline-flex items-center gap-1 rounded-full bg-primary/10 px-2.5 py-1 text-xs font-medium text-primary"
                            >
                                {{ props.user.data.level.icon }}
                                {{ props.user.data.level.name }}
                            </span>
                        </div>
                        <div
                            class="flex flex-wrap items-center gap-4 text-sm text-muted-foreground"
                        >
                            <span class="flex items-center gap-1">
                                <Calendar class="size-4" />
                                Miembro desde
                                {{
                                    formatMemberSince(
                                        props.user.data.created_at,
                                    )
                                }}
                            </span>
                            <span class="flex items-center gap-1">
                                <Star class="size-4" />
                                {{
                                    props.user.data.total_points.toLocaleString()
                                }}
                                puntos
                            </span>
                            <span>
                                {{ props.user.data.photos_count }}
                                {{
                                    props.user.data.photos_count === 1
                                        ? 'foto'
                                        : 'fotos'
                                }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Badges Section -->
            <div v-if="props.user.data.badges.length > 0" class="space-y-3">
                <div class="flex items-center gap-2">
                    <Award class="size-5 text-muted-foreground" />
                    <h2 class="text-lg font-semibold tracking-tight">
                        Insignias
                    </h2>
                </div>
                <div
                    class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4"
                >
                    <div
                        v-for="badge in props.user.data.badges"
                        :key="badge.id"
                        class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
                    >
                        <p class="text-sm font-medium">{{ badge.name }}</p>
                        <p class="mt-1 text-xs text-muted-foreground">
                            {{ badge.description }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Photos Section -->
            <div class="space-y-3">
                <h2 class="text-lg font-semibold tracking-tight">
                    Fotos subidas
                </h2>

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
                        <div
                            class="relative aspect-[4/3] overflow-hidden bg-muted"
                        >
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
                        Este usuario no ha subido fotos todavia.
                    </p>
                </div>

                <!-- Págination -->
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
        </div>
    </AppLayout>
</template>
