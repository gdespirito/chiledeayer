<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Camera, ImagePlus, Map, MapPin, Trophy } from 'lucide-vue-next';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { home, leaderboard, map } from '@/routes';
import { create as photosCreate, index as photosIndex } from '@/routes/photos';
import { index as placesIndex } from '@/routes/places';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Inicio',
        href: home(),
    },
];

const page = usePage();
const user = computed(() => page.props.auth.user);

const quickLinks = [
    {
        title: 'Explorar Fotos',
        href: photosIndex(),
        icon: Camera,
        description: 'Navega el archivo completo',
    },
    {
        title: 'Lugares',
        href: placesIndex(),
        icon: MapPin,
        description: 'Fotos por ubicación',
    },
    {
        title: 'Mapa',
        href: map(),
        icon: Map,
        description: 'Vista geográfica',
    },
    {
        title: 'Tabla de Honor',
        href: leaderboard(),
        icon: Trophy,
        description: 'Ranking de contribuidores',
    },
];
</script>

<template>
    <Head title="Panel" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4 lg:p-6">
            <!-- Welcome -->
            <div
                class="rounded-xl border border-stone-200/70 bg-gradient-to-r from-amber-50/80 to-stone-50/50 p-6 lg:p-8 dark:border-stone-800 dark:from-amber-950/20 dark:to-stone-900/20"
            >
                <h1
                    class="text-2xl font-semibold tracking-tight text-stone-900 dark:text-stone-100"
                >
                    Bienvenido, {{ user.name }}
                </h1>
                <p class="mt-2 text-stone-600 dark:text-stone-400">
                    Gracias por ser parte del Archivo de Chile. Tu contribución
                    ayuda a preservar nuestra memoria visual.
                </p>
                <div class="mt-6">
                    <Button as-child>
                        <Link :href="photosCreate()">
                            <ImagePlus class="mr-2 size-4" />
                            Subir foto
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- Quick Links -->
            <div>
                <h2
                    class="mb-4 text-lg font-semibold tracking-tight text-stone-900 dark:text-stone-100"
                >
                    Accesos rápidos
                </h2>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <Link
                        v-for="link in quickLinks"
                        :key="link.title"
                        :href="link.href"
                        class="group flex items-start gap-4 rounded-xl border border-stone-200/70 p-4 transition-colors hover:border-amber-300 hover:bg-amber-50/30 dark:border-stone-800 dark:hover:border-amber-700 dark:hover:bg-amber-950/10"
                    >
                        <div
                            class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-stone-100 transition-colors group-hover:bg-amber-100 dark:bg-stone-800 dark:group-hover:bg-amber-900/30"
                        >
                            <component
                                :is="link.icon"
                                class="size-5 text-stone-500 transition-colors group-hover:text-amber-600 dark:text-stone-400 dark:group-hover:text-amber-400"
                            />
                        </div>
                        <div>
                            <p
                                class="text-sm font-medium text-stone-900 dark:text-stone-100"
                            >
                                {{ link.title }}
                            </p>
                            <p
                                class="mt-0.5 text-xs text-stone-500 dark:text-stone-500"
                            >
                                {{ link.description }}
                            </p>
                        </div>
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
