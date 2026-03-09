<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowRight,
    Camera,
    ChevronUp,
    ImagePlus,
    Map,
    MapPin,
    MessageCircle,
    Search,
    Tag,
    ThumbsUp,
    Trophy,
    Users,
} from 'lucide-vue-next';
import JsonLd from '@/components/JsonLd.vue';
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

const steps = [
    { num: '1', text: 'Crea tu cuenta gratis' },
    { num: '2', text: 'Sube o explora fotos históricas' },
    { num: '3', text: 'Contribuye con información y votos' },
    { num: '4', text: 'Gana puntos y reconocimiento' },
];

const howToSchema = {
    '@context': 'https://schema.org',
    '@type': 'HowTo',
    name: 'Cómo contribuir al Archivo de Chile',
    description:
        'Guía para contribuir al archivo fotográfico histórico de Chile. Sube fotografías históricas, identifica lugares, etiqueta y ayuda a preservar la memoria visual.',
    step: steps.map((s, i) => ({
        '@type': 'HowToStep',
        position: i + 1,
        name: s.text,
    })),
};
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
    <JsonLd :schema="howToSchema" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col">
            <!-- Hero -->
            <section
                class="relative flex flex-col items-center justify-center overflow-hidden bg-gradient-to-b from-amber-50/50 to-transparent px-4 py-20 text-center lg:py-28 dark:from-amber-950/10 dark:to-transparent"
            >
                <div
                    class="mb-6 flex size-20 items-center justify-center rounded-2xl bg-amber-100 dark:bg-amber-900/30"
                >
                    <Users class="size-10 text-amber-600 dark:text-amber-400" />
                </div>
                <h1
                    class="mb-4 text-4xl font-bold tracking-tight text-stone-900 lg:text-5xl dark:text-stone-100"
                >
                    Construyamos juntos el archivo
                    <br class="hidden sm:block" />
                    fotográfico de Chile
                </h1>
                <p
                    class="mb-6 max-w-2xl text-lg text-stone-600 dark:text-stone-400"
                >
                    El Archivo de Chile es un proyecto colaborativo y gratuito.
                    Cualquier persona puede contribuir a preservar la memoria
                    visual del país, desde la comodidad de su casa o recorriendo
                    las calles.
                </p>
                <div class="flex flex-col items-center gap-3 sm:flex-row">
                    <Button as-child size="lg">
                        <Link :href="register()">
                            Crear cuenta gratis
                            <ArrowRight class="ml-2 size-4" />
                        </Link>
                    </Button>
                    <Button as-child variant="outline" size="lg">
                        <Link :href="login()"> Ya tengo cuenta </Link>
                    </Button>
                </div>
            </section>

            <!-- How it works -->
            <section class="px-4 py-16 lg:px-8">
                <div class="mx-auto max-w-4xl">
                    <h2
                        class="mb-10 text-center text-2xl font-semibold tracking-tight text-stone-900 dark:text-stone-100"
                    >
                        ¿Cómo funciona?
                    </h2>
                    <div class="grid grid-cols-2 gap-6 lg:grid-cols-4">
                        <div
                            v-for="step in steps"
                            :key="step.num"
                            class="flex flex-col items-center text-center"
                        >
                            <div
                                class="mb-3 flex size-12 items-center justify-center rounded-full bg-amber-500 text-lg font-bold text-white"
                            >
                                {{ step.num }}
                            </div>
                            <p
                                class="text-sm font-medium text-stone-700 dark:text-stone-300"
                            >
                                {{ step.text }}
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Feature 1: Upload photos -->
            <section
                class="border-t border-stone-200/70 bg-stone-50/50 px-4 py-16 lg:px-8 dark:border-stone-800 dark:bg-stone-900/20"
            >
                <div
                    class="mx-auto grid max-w-5xl items-center gap-12 md:grid-cols-2"
                >
                    <div>
                        <div
                            class="mb-4 flex size-12 items-center justify-center rounded-xl bg-amber-100 dark:bg-amber-900/30"
                        >
                            <Camera
                                class="size-6 text-amber-600 dark:text-amber-400"
                            />
                        </div>
                        <h2
                            class="mb-3 text-2xl font-semibold tracking-tight text-stone-900 dark:text-stone-100"
                        >
                            Sube fotografías históricas
                        </h2>
                        <p
                            class="mb-4 leading-relaxed text-stone-600 dark:text-stone-400"
                        >
                            ¿Tienes fotos antiguas en casa? ¿Postales, álbumes
                            familiares, o recortes de diario? Cada imagen cuenta
                            una historia. Digitalízalas y compártelas con todo
                            Chile.
                        </p>
                        <ul
                            class="space-y-2 text-sm text-stone-500 dark:text-stone-400"
                        >
                            <li class="flex items-start gap-2">
                                <ChevronUp
                                    class="mt-0.5 size-4 shrink-0 text-amber-500"
                                />
                                Fotos familiares, postales, recortes de prensa
                            </li>
                            <li class="flex items-start gap-2">
                                <ChevronUp
                                    class="mt-0.5 size-4 shrink-0 text-amber-500"
                                />
                                Imágenes de dominio público o con permiso
                            </li>
                            <li class="flex items-start gap-2">
                                <ChevronUp
                                    class="mt-0.5 size-4 shrink-0 text-amber-500"
                                />
                                Sube una o muchas fotos a la vez
                            </li>
                        </ul>
                    </div>
                    <!-- Visual -->
                    <div
                        class="flex items-center justify-center rounded-2xl border border-stone-200 bg-white p-8 shadow-sm dark:border-stone-700 dark:bg-stone-800/50"
                    >
                        <div class="relative">
                            <div
                                class="flex size-32 items-center justify-center rounded-2xl bg-amber-50 dark:bg-amber-900/20"
                            >
                                <Camera
                                    class="size-16 text-amber-400 dark:text-amber-600"
                                />
                            </div>
                            <div
                                class="absolute -top-3 -right-3 flex size-10 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/30"
                            >
                                <ImagePlus
                                    class="size-5 text-green-600 dark:text-green-400"
                                />
                            </div>
                            <div
                                class="absolute -bottom-2 -left-4 flex size-8 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900/30"
                            >
                                <Search
                                    class="size-4 text-blue-600 dark:text-blue-400"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Feature 2: Identify and tag -->
            <section class="px-4 py-16 lg:px-8">
                <div
                    class="mx-auto grid max-w-5xl items-center gap-12 md:grid-cols-2"
                >
                    <!-- Visual -->
                    <div
                        class="order-2 flex items-center justify-center rounded-2xl border border-stone-200 bg-white p-8 shadow-sm md:order-1 dark:border-stone-700 dark:bg-stone-800/50"
                    >
                        <div class="relative">
                            <div
                                class="flex size-32 items-center justify-center rounded-2xl bg-blue-50 dark:bg-blue-900/20"
                            >
                                <Map
                                    class="size-16 text-blue-400 dark:text-blue-600"
                                />
                            </div>
                            <div
                                class="absolute -top-2 -right-4 flex size-10 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-900/30"
                            >
                                <MapPin
                                    class="size-5 text-amber-600 dark:text-amber-400"
                                />
                            </div>
                            <div
                                class="absolute -bottom-3 -left-3 flex size-9 items-center justify-center rounded-full bg-purple-100 dark:bg-purple-900/30"
                            >
                                <Tag
                                    class="size-4 text-purple-600 dark:text-purple-400"
                                />
                            </div>
                        </div>
                    </div>
                    <div class="order-1 md:order-2">
                        <div
                            class="mb-4 flex size-12 items-center justify-center rounded-xl bg-blue-100 dark:bg-blue-900/30"
                        >
                            <MapPin
                                class="size-6 text-blue-600 dark:text-blue-400"
                            />
                        </div>
                        <h2
                            class="mb-3 text-2xl font-semibold tracking-tight text-stone-900 dark:text-stone-100"
                        >
                            Identifica lugares y etiqueta
                        </h2>
                        <p
                            class="mb-4 leading-relaxed text-stone-600 dark:text-stone-400"
                        >
                            Muchas fotos antiguas no tienen información sobre
                            dónde fueron tomadas. Si reconoces un lugar, una
                            calle o un edificio, tu conocimiento es invaluable.
                            Ayuda a ubicar estas fotos en el mapa.
                        </p>
                        <ul
                            class="space-y-2 text-sm text-stone-500 dark:text-stone-400"
                        >
                            <li class="flex items-start gap-2">
                                <ChevronUp
                                    class="mt-0.5 size-4 shrink-0 text-blue-500"
                                />
                                Geolocaliza fotos para el mapa interactivo
                            </li>
                            <li class="flex items-start gap-2">
                                <ChevronUp
                                    class="mt-0.5 size-4 shrink-0 text-blue-500"
                                />
                                Agrega etiquetas y contexto histórico
                            </li>
                            <li class="flex items-start gap-2">
                                <ChevronUp
                                    class="mt-0.5 size-4 shrink-0 text-blue-500"
                                />
                                Identifica personas en las fotografías
                            </li>
                        </ul>
                    </div>
                </div>
            </section>

            <!-- Feature 3: Vote and compare -->
            <section
                class="border-t border-stone-200/70 bg-stone-50/50 px-4 py-16 lg:px-8 dark:border-stone-800 dark:bg-stone-900/20"
            >
                <div
                    class="mx-auto grid max-w-5xl items-center gap-12 md:grid-cols-2"
                >
                    <div>
                        <div
                            class="mb-4 flex size-12 items-center justify-center rounded-xl bg-green-100 dark:bg-green-900/30"
                        >
                            <ThumbsUp
                                class="size-6 text-green-600 dark:text-green-400"
                            />
                        </div>
                        <h2
                            class="mb-3 text-2xl font-semibold tracking-tight text-stone-900 dark:text-stone-100"
                        >
                            Vota, comenta y compara
                        </h2>
                        <p
                            class="mb-4 leading-relaxed text-stone-600 dark:text-stone-400"
                        >
                            No necesitas tener fotos propias para contribuir.
                            Cada voto ayuda a destacar las mejores fotos. Cada
                            comentario agrega contexto valioso. Y si puedes
                            fotografiar el mismo lugar hoy, la comparación
                            antes/después es fascinante.
                        </p>
                        <ul
                            class="space-y-2 text-sm text-stone-500 dark:text-stone-400"
                        >
                            <li class="flex items-start gap-2">
                                <ChevronUp
                                    class="mt-0.5 size-4 shrink-0 text-green-500"
                                />
                                Vota para destacar las mejores fotos
                            </li>
                            <li class="flex items-start gap-2">
                                <ChevronUp
                                    class="mt-0.5 size-4 shrink-0 text-green-500"
                                />
                                Comenta con datos históricos que conozcas
                            </li>
                            <li class="flex items-start gap-2">
                                <ChevronUp
                                    class="mt-0.5 size-4 shrink-0 text-green-500"
                                />
                                Sube fotos actuales del mismo lugar
                            </li>
                        </ul>
                    </div>
                    <!-- Visual -->
                    <div
                        class="flex items-center justify-center rounded-2xl border border-stone-200 bg-white p-8 shadow-sm dark:border-stone-700 dark:bg-stone-800/50"
                    >
                        <div class="relative">
                            <div
                                class="flex size-32 items-center justify-center rounded-2xl bg-green-50 dark:bg-green-900/20"
                            >
                                <MessageCircle
                                    class="size-16 text-green-400 dark:text-green-600"
                                />
                            </div>
                            <div
                                class="absolute -top-3 -right-3 flex size-10 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-900/30"
                            >
                                <ThumbsUp
                                    class="size-5 text-amber-600 dark:text-amber-400"
                                />
                            </div>
                            <div
                                class="absolute -bottom-2 -left-4 flex size-8 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900/30"
                            >
                                <ImagePlus
                                    class="size-4 text-blue-600 dark:text-blue-400"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Feature 4: Gamification -->
            <section class="px-4 py-16 lg:px-8">
                <div class="mx-auto max-w-4xl text-center">
                    <div
                        class="mx-auto mb-4 flex size-12 items-center justify-center rounded-xl bg-purple-100 dark:bg-purple-900/30"
                    >
                        <Trophy
                            class="size-6 text-purple-600 dark:text-purple-400"
                        />
                    </div>
                    <h2
                        class="mb-3 text-2xl font-semibold tracking-tight text-stone-900 dark:text-stone-100"
                    >
                        Gana reconocimiento por tus aportes
                    </h2>
                    <p
                        class="mx-auto mb-8 max-w-2xl leading-relaxed text-stone-600 dark:text-stone-400"
                    >
                        Cada contribución suma puntos. Sube fotos, etiqueta
                        lugares, comenta y vota para subir en la Tabla de Honor.
                        Desbloquea insignias especiales por tus logros y
                        conviértete en parte fundamental de esta comunidad.
                    </p>
                    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                        <div
                            class="rounded-xl border border-stone-200/70 bg-stone-50/50 p-4 dark:border-stone-800 dark:bg-stone-900/30"
                        >
                            <p
                                class="text-2xl font-bold text-amber-600 dark:text-amber-400"
                            >
                                +10
                            </p>
                            <p class="text-xs text-stone-500">
                                por subir una foto
                            </p>
                        </div>
                        <div
                            class="rounded-xl border border-stone-200/70 bg-stone-50/50 p-4 dark:border-stone-800 dark:bg-stone-900/30"
                        >
                            <p
                                class="text-2xl font-bold text-blue-600 dark:text-blue-400"
                            >
                                +5
                            </p>
                            <p class="text-xs text-stone-500">
                                por editar metadata
                            </p>
                        </div>
                        <div
                            class="rounded-xl border border-stone-200/70 bg-stone-50/50 p-4 dark:border-stone-800 dark:bg-stone-900/30"
                        >
                            <p
                                class="text-2xl font-bold text-green-600 dark:text-green-400"
                            >
                                +3
                            </p>
                            <p class="text-xs text-stone-500">por comentar</p>
                        </div>
                        <div
                            class="rounded-xl border border-stone-200/70 bg-stone-50/50 p-4 dark:border-stone-800 dark:bg-stone-900/30"
                        >
                            <p
                                class="text-2xl font-bold text-purple-600 dark:text-purple-400"
                            >
                                +1
                            </p>
                            <p class="text-xs text-stone-500">por votar</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Final CTA -->
            <section
                class="border-t border-stone-200/70 bg-gradient-to-b from-amber-50/80 to-transparent px-4 py-20 dark:border-stone-800 dark:from-amber-950/10 dark:to-transparent"
            >
                <div class="mx-auto max-w-2xl text-center">
                    <h2
                        class="mb-3 text-3xl font-bold tracking-tight text-stone-900 dark:text-stone-100"
                    >
                        Comienza a contribuir hoy
                    </h2>
                    <p class="mb-8 text-stone-600 dark:text-stone-400">
                        Es completamente gratis. Únete a la comunidad que está
                        preservando la historia visual de Chile, foto a foto.
                    </p>
                    <div
                        class="flex flex-col items-center justify-center gap-3 sm:flex-row"
                    >
                        <Button as-child size="lg">
                            <Link :href="register()">
                                Crear cuenta gratis
                                <ArrowRight class="ml-2 size-4" />
                            </Link>
                        </Button>
                        <Button as-child variant="outline" size="lg">
                            <Link :href="login()"> Ya tengo cuenta </Link>
                        </Button>
                    </div>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
