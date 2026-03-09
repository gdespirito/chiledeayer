<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import {
    ArrowRight,
    Camera,
    Heart,
    Map,
    MapPin,
    MessageCircle,
    Search,
    Star,
    Tag,
    Trophy,
    Users,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import PhotoCard from '@/components/PhotoCard.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import { leaderboard, map } from '@/routes';
import { index as photosIndex } from '@/routes/photos';
import { index as placesIndex } from '@/routes/places';
import { index as tagsIndex } from '@/routes/tags';
import type { Photo } from '@/types';

type Props = {
    photoOfTheDay: { data: Photo } | null;
    latest: { data: Photo[] };
    popular: { data: Photo[] };
    needsHelp: { data: Photo[] };
};

const props = defineProps<Props>();

const page = usePage();
const isGuest = computed(() => !page.props.auth?.user);

const searchQuery = ref('');

function submitSearch(): void {
    if (searchQuery.value.trim()) {
        router.get('/search', { q: searchQuery.value.trim() });
    }
}

function getThumbnail(photo: Photo): string | null {
    const medium = photo.files.find((f) => f.variant === 'medium');
    const original = photo.files.find((f) => f.variant === 'original');
    const thumb = photo.files.find((f) => f.variant === 'thumb');

    return medium?.url ?? original?.url ?? thumb?.url ?? null;
}

type HelpNeeded = {
    label: string;
    icon: typeof MapPin;
    color: string;
};

function getHelpNeeded(photo: Photo): HelpNeeded[] {
    const needs: HelpNeeded[] = [];
    if (!photo.place) {
        needs.push({
            label: 'Necesita ubicación',
            icon: MapPin,
            color: 'text-blue-600 bg-blue-50 dark:text-blue-400 dark:bg-blue-950/30',
        });
    }
    if (photo.tags_count !== undefined && photo.tags_count === 0) {
        needs.push({
            label: 'Necesita etiquetas',
            icon: Tag,
            color: 'text-purple-600 bg-purple-50 dark:text-purple-400 dark:bg-purple-950/30',
        });
    }
    if (photo.comments_count !== undefined && photo.comments_count === 0) {
        needs.push({
            label: 'Sin comentarios',
            icon: MessageCircle,
            color: 'text-green-600 bg-green-50 dark:text-green-400 dark:bg-green-950/30',
        });
    }
    return needs;
}

const exploreLinks = [
    {
        title: 'Explorar Fotos',
        href: photosIndex(),
        icon: Camera,
        description: 'Navega el archivo fotográfico completo',
    },
    {
        title: 'Lugares',
        href: placesIndex(),
        icon: MapPin,
        description: 'Descubre fotos por lugar',
    },
    {
        title: 'Etiquetas',
        href: tagsIndex(),
        icon: Tag,
        description: 'Busca por temas y categorías',
    },
    {
        title: 'Mapa',
        href: map(),
        icon: Map,
        description: 'Explora fotos en el mapa',
    },
    {
        title: 'Tabla de Honor',
        href: leaderboard(),
        icon: Trophy,
        description: 'Nuestros mayores contribuidores',
    },
];
</script>

<template>
    <Head title="Inicio">
        <meta
            head-key="description"
            name="description"
            content="Explora fotografías históricas de Chile. Un archivo colaborativo del patrimonio visual chileno donde puedes descubrir, compartir y preservar nuestra memoria visual."
        />
        <meta
            property="og:title"
            content="Archivo de Chile — Fotografías históricas"
        />
        <meta
            property="og:description"
            content="Descubre la historia visual de Chile a través de fotografías históricas. Explora, contribuye y ayuda a preservar nuestra memoria visual."
        />
        <meta property="og:type" content="website" />
    </Head>

    <AppLayout>
        <div class="flex flex-1 flex-col">
            <!-- Hero Section -->
            <section
                class="relative flex flex-col items-center justify-center bg-gradient-to-b from-amber-50/50 to-transparent px-4 py-16 text-center lg:py-24 dark:from-amber-950/10 dark:to-transparent"
            >
                <h1
                    class="mb-4 text-4xl font-bold tracking-tight text-stone-900 lg:text-5xl dark:text-stone-100"
                >
                    Archivo de Chile
                </h1>
                <p
                    class="mb-2 max-w-2xl text-lg text-stone-600 dark:text-stone-400"
                >
                    Descubre la historia visual de Chile
                </p>
                <p
                    class="mb-8 max-w-2xl text-base text-stone-500 dark:text-stone-500"
                >
                    Un archivo colaborativo de fotografías históricas. Explora,
                    contribuye y ayuda a preservar nuestra memoria visual.
                </p>

                <!-- Search Input -->
                <form
                    class="flex w-full max-w-lg gap-2"
                    @submit.prevent="submitSearch"
                >
                    <div class="relative flex-1">
                        <Search
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
            </section>

            <!-- Explore Links -->
            <section class="px-4 pb-12 lg:px-8">
                <div class="mx-auto max-w-6xl">
                    <div
                        class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5"
                    >
                        <Link
                            v-for="link in exploreLinks"
                            :key="link.title"
                            :href="link.href"
                            class="group flex flex-col items-center gap-2 rounded-xl border border-stone-200/70 bg-stone-50/50 p-4 text-center transition-colors hover:border-amber-300 hover:bg-amber-50/50 dark:border-stone-800 dark:bg-stone-900/30 dark:hover:border-amber-700 dark:hover:bg-amber-950/20"
                        >
                            <component
                                :is="link.icon"
                                class="size-6 text-stone-400 transition-colors group-hover:text-amber-600 dark:text-stone-500 dark:group-hover:text-amber-400"
                            />
                            <span
                                class="text-sm font-medium text-stone-700 dark:text-stone-300"
                            >
                                {{ link.title }}
                            </span>
                        </Link>
                    </div>
                </div>
            </section>

            <!-- Photo of the Day -->
            <section v-if="props.photoOfTheDay" class="px-4 pb-12 lg:px-8">
                <div class="mx-auto max-w-6xl">
                    <div class="mb-6 flex items-center gap-2">
                        <Star class="size-5 text-amber-500" />
                        <h2 class="text-2xl font-semibold tracking-tight">
                            Foto del día
                        </h2>
                    </div>
                    <Link
                        :href="`/photos/${props.photoOfTheDay.data.id}`"
                        class="group grid overflow-hidden rounded-2xl border border-sidebar-border/70 bg-card shadow-sm transition-shadow hover:shadow-lg md:grid-cols-2 dark:border-sidebar-border"
                    >
                        <div
                            class="relative aspect-[4/3] overflow-hidden bg-muted"
                        >
                            <img
                                v-if="getThumbnail(props.photoOfTheDay.data)"
                                :src="getThumbnail(props.photoOfTheDay.data)!"
                                :alt="props.photoOfTheDay.data.description"
                                class="size-full object-cover transition-transform duration-500 group-hover:scale-105"
                            />
                        </div>
                        <div
                            class="flex flex-col justify-center gap-4 p-6 lg:p-8"
                        >
                            <p
                                class="text-lg leading-relaxed font-medium lg:text-xl"
                            >
                                {{
                                    props.photoOfTheDay.data.description
                                }}
                            </p>
                            <div
                                class="flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-muted-foreground"
                            >
                                <span
                                    v-if="props.photoOfTheDay.data.place"
                                    class="flex items-center gap-1"
                                >
                                    <MapPin class="size-3.5" />
                                    {{
                                        props.photoOfTheDay.data.place.name
                                    }}
                                </span>
                                <span>&middot;</span>
                                <span>~{{ props.photoOfTheDay.data.year_from }}</span>
                            </div>
                            <div
                                v-if="props.photoOfTheDay.data.tags.length > 0"
                                class="flex flex-wrap gap-1.5"
                            >
                                <span
                                    v-for="tag in props.photoOfTheDay.data.tags.slice(0, 5)"
                                    :key="tag.id"
                                    class="rounded-full bg-stone-100 px-2.5 py-0.5 text-xs text-stone-600 dark:bg-stone-800 dark:text-stone-400"
                                >
                                    {{ tag.name }}
                                </span>
                            </div>
                            <span
                                class="mt-2 inline-flex items-center gap-1 text-sm font-medium text-amber-600 group-hover:text-amber-700 dark:text-amber-400 dark:group-hover:text-amber-300"
                            >
                                Ver foto
                                <ArrowRight class="size-4" />
                            </span>
                        </div>
                    </Link>
                </div>
            </section>

            <!-- Latest Photos -->
            <section
                v-if="props.latest.data.length > 0"
                class="px-4 pb-12 lg:px-8"
            >
                <div class="mx-auto max-w-6xl">
                    <div class="mb-6 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <Camera class="size-5 text-amber-600" />
                            <h2 class="text-2xl font-semibold tracking-tight">
                                Recién agregadas
                            </h2>
                        </div>
                        <Link
                            :href="photosIndex()"
                            class="flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground"
                        >
                            Ver todas
                            <ArrowRight class="size-4" />
                        </Link>
                    </div>
                    <div
                        class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4"
                    >
                        <PhotoCard
                            v-for="photo in props.latest.data"
                            :key="photo.id"
                            :photo="photo"
                        />
                    </div>
                </div>
            </section>

            <!-- Popular Photos -->
            <section
                v-if="props.popular.data.length > 0"
                class="px-4 pb-12 lg:px-8"
            >
                <div class="mx-auto max-w-6xl">
                    <div class="mb-6 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <Star class="size-5 text-amber-500" />
                            <h2 class="text-2xl font-semibold tracking-tight">
                                Más populares
                            </h2>
                        </div>
                        <Link
                            :href="photosIndex()"
                            class="flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground"
                        >
                            Ver todas
                            <ArrowRight class="size-4" />
                        </Link>
                    </div>
                    <div
                        class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4"
                    >
                        <PhotoCard
                            v-for="photo in props.popular.data"
                            :key="photo.id"
                            :photo="photo"
                        />
                    </div>
                </div>
            </section>

            <!-- Needs Help -->
            <section
                v-if="props.needsHelp.data.length > 0"
                class="px-4 pb-12 lg:px-8"
            >
                <div class="mx-auto max-w-6xl">
                    <div class="mb-6 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <Users class="size-5 text-green-500" />
                            <h2 class="text-2xl font-semibold tracking-tight">
                                Necesitan tu ayuda
                            </h2>
                        </div>
                        <Link
                            :href="photosIndex()"
                            class="flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground"
                        >
                            Ver todas
                            <ArrowRight class="size-4" />
                        </Link>
                    </div>
                    <div
                        class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4"
                    >
                        <Link
                            v-for="photo in props.needsHelp.data"
                            :key="photo.id"
                            :href="`/photos/${photo.id}`"
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
                            </div>
                            <div class="space-y-2 p-4">
                                <p class="line-clamp-2 text-sm font-medium">
                                    {{ photo.description }}
                                </p>
                                <div class="flex flex-wrap gap-1.5">
                                    <span
                                        v-for="need in getHelpNeeded(photo)"
                                        :key="need.label"
                                        class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-medium"
                                        :class="need.color"
                                    >
                                        <component
                                            :is="need.icon"
                                            class="size-3"
                                        />
                                        {{ need.label }}
                                    </span>
                                </div>
                            </div>
                        </Link>
                    </div>
                </div>
            </section>

            <!-- Contribute CTA banner (guests only) -->
            <section v-if="isGuest" class="px-4 pb-12 lg:px-8">
                <div
                    class="mx-auto max-w-6xl overflow-hidden rounded-xl border border-amber-200 bg-gradient-to-r from-amber-50 to-orange-50 dark:border-amber-900/50 dark:from-amber-950/20 dark:to-orange-950/20"
                >
                    <div
                        class="flex flex-col items-center gap-6 p-8 text-center md:flex-row md:text-left"
                    >
                        <div
                            class="flex size-16 shrink-0 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-900/30"
                        >
                            <Heart
                                class="size-8 text-amber-600 dark:text-amber-400"
                            />
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold tracking-tight">
                                ¿Tienes fotos históricas? ¡Compártelas!
                            </h3>
                            <p class="mt-1 text-sm text-muted-foreground">
                                Ayuda a preservar la memoria visual de Chile.
                                Sube fotos, identifica lugares, comenta y gana
                                reconocimiento.
                            </p>
                        </div>
                        <div class="flex shrink-0 gap-3">
                            <Link href="/contribuir">
                                <Button
                                    class="cursor-pointer bg-amber-500 text-white hover:bg-amber-600 dark:bg-amber-600 dark:hover:bg-amber-700"
                                >
                                    Descubre cómo contribuir
                                </Button>
                            </Link>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Empty state when no photos exist -->
            <section
                v-if="props.latest.data.length === 0 && !props.photoOfTheDay"
                class="px-4 pb-12 lg:px-8"
            >
                <div
                    class="mx-auto flex max-w-6xl flex-col items-center justify-center rounded-xl border border-dashed border-stone-300 p-12 dark:border-stone-700"
                >
                    <Camera
                        class="mb-4 size-12 text-stone-400 dark:text-stone-600"
                    />
                    <p class="text-lg font-medium">
                        Aún no hay fotos en el archivo
                    </p>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Sé el primero en contribuir subiendo una foto histórica.
                    </p>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
