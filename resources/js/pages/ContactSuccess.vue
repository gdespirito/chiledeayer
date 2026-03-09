<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { CheckCircle, Home, Mail } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { create as contactCreate } from '@/routes/contact';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Contacto',
        href: contactCreate(),
    },
    {
        title: 'Mensaje enviado',
        href: '/contacto-success',
    },
];
</script>

<template>
    <Head title="Mensaje enviado">
        <meta name="robots" content="noindex" />
    </Head>

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col items-center justify-center gap-6 p-4">
            <div class="mx-auto max-w-md text-center">
                <div
                    class="mx-auto mb-6 flex size-20 items-center justify-center rounded-full bg-green-100"
                >
                    <CheckCircle class="size-10 text-green-600" />
                </div>

                <h1 class="text-2xl font-semibold tracking-tight">
                    ¡Mensaje enviado!
                </h1>

                <p class="mt-3 text-muted-foreground">
                    Gracias por contactarnos. Revisaremos tu mensaje y te
                    responderemos lo antes posible al correo que nos indicaste.
                </p>

                <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:justify-center">
                    <Button as-child>
                        <Link href="/">
                            <Home class="mr-2 size-4" />
                            Volver al inicio
                        </Link>
                    </Button>
                    <Button as-child variant="outline">
                        <Link :href="contactCreate()">
                            <Mail class="mr-2 size-4" />
                            Enviar otro mensaje
                        </Link>
                    </Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
