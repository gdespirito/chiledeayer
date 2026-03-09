<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    Camera,
    ImagePlus,
    MapPin,
    Tag,
    ThumbsUp,
    Trophy,
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { contribute, login, register } from '@/routes';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Contribuir',
        href: contribute(),
    },
];

const features = [
    {
        icon: Camera,
        title: 'Subir fotografías',
        description:
            'Comparte fotos históricas de tu colección familiar, archivos personales o fuentes de dominio público.',
    },
    {
        icon: MapPin,
        title: 'Identificar lugares',
        description:
            'Ayuda a geolocalizar fotos para que aparezcan en el mapa interactivo.',
    },
    {
        icon: Tag,
        title: 'Etiquetar y describir',
        description:
            'Agrega etiquetas, describe el contexto histórico y mejora la información de las fotos.',
    },
    {
        icon: ThumbsUp,
        title: 'Votar y comentar',
        description:
            'Destaca las mejores fotos y comparte tu conocimiento histórico.',
    },
    {
        icon: ImagePlus,
        title: 'Subir fotos del ahora',
        description:
            'Captura el mismo lugar en la actualidad para comparar cómo ha cambiado.',
    },
    {
        icon: Trophy,
        title: 'Ganar reconocimiento',
        description:
            'Acumula puntos e insignias por tus contribuciones en la Tabla de Honor.',
    },
];
</script>

<template>
    <Head title="Contribuir">
        <meta
            head-key="description"
            name="description"
            content="Contribuye al Archivo de Chile. Sube fotografías históricas, identifica lugares, etiqueta y ayuda a preservar la memoria visual de Chile."
        />
        <meta property="og:title" content="Contribuir — Archivo de Chile" />
        <meta
            property="og:description"
            content="Descubre cómo puedes contribuir al Archivo de Chile. Sube fotos históricas, identifica lugares y gana reconocimiento."
        />
        <meta property="og:type" content="website" />
    </Head>

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col">
            <!-- Hero Section -->
            <section
                class="relative flex flex-col items-center justify-center bg-gradient-to-b from-amber-50/50 to-transparent px-4 py-16 text-center lg:py-24 dark:from-amber-950/10 dark:to-transparent"
            >
                <h1
                    class="mb-4 text-4xl font-bold tracking-tight text-stone-900 lg:text-5xl dark:text-stone-100"
                >
                    Contribuye al Archivo de Chile
                </h1>
                <p
                    class="mb-2 max-w-2xl text-lg text-stone-600 dark:text-stone-400"
                >
                    Ayuda a preservar la memoria visual de Chile
                </p>
                <p
                    class="max-w-2xl text-base text-stone-500 dark:text-stone-500"
                >
                    El Archivo de Chile es un proyecto colaborativo y gratuito.
                    Cualquier persona puede registrarse y contribuir a construir
                    el mayor archivo fotográfico histórico de Chile.
                </p>
            </section>

            <!-- Features Section -->
            <section class="px-4 pb-12 lg:px-8">
                <div class="mx-auto max-w-6xl">
                    <div class="mb-8 text-center">
                        <h2
                            class="text-2xl font-semibold tracking-tight text-stone-900 dark:text-stone-100"
                        >
                            ¿Qué puedes hacer?
                        </h2>
                    </div>

                    <div
                        class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3"
                    >
                        <div
                            v-for="feature in features"
                            :key="feature.title"
                            class="flex flex-col gap-3 rounded-xl border border-stone-200/70 bg-stone-50/50 p-6 transition-colors hover:border-amber-300 hover:bg-amber-50/50 dark:border-stone-800 dark:bg-stone-900/30 dark:hover:border-amber-700 dark:hover:bg-amber-950/20"
                        >
                            <div
                                class="flex size-10 items-center justify-center rounded-lg bg-amber-100 dark:bg-amber-900/30"
                            >
                                <component
                                    :is="feature.icon"
                                    class="size-5 text-amber-600 dark:text-amber-400"
                                />
                            </div>
                            <h3
                                class="text-base font-medium text-stone-900 dark:text-stone-100"
                            >
                                {{ feature.title }}
                            </h3>
                            <p
                                class="text-sm leading-relaxed text-stone-500 dark:text-stone-400"
                            >
                                {{ feature.description }}
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- CTA Section -->
            <section class="px-4 pb-16 lg:px-8">
                <div
                    class="mx-auto max-w-2xl rounded-2xl border border-amber-200/70 bg-gradient-to-b from-amber-50/80 to-amber-50/30 p-8 text-center dark:border-amber-800/30 dark:from-amber-950/20 dark:to-amber-950/5"
                >
                    <h2
                        class="mb-2 text-xl font-semibold tracking-tight text-stone-900 dark:text-stone-100"
                    >
                        Comienza a contribuir hoy
                    </h2>
                    <p class="mb-6 text-sm text-stone-500 dark:text-stone-400">
                        Es completamente gratis. Crea tu cuenta y empieza a
                        compartir la historia visual de Chile.
                    </p>
                    <div
                        class="flex flex-col items-center justify-center gap-3 sm:flex-row"
                    >
                        <Button as-child size="lg">
                            <Link :href="register()">
                                Crear cuenta gratis
                            </Link>
                        </Button>
                        <Button as-child variant="outline" size="lg">
                            <Link :href="login()"> Iniciar sesión </Link>
                        </Button>
                    </div>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
