<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import { Mail, Phone, Send, User } from 'lucide-vue-next';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { create as contactCreate } from '@/routes/contact';
import { store as contactStore } from '@/routes/contact';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Contacto',
        href: contactCreate(),
    },
];

const form = useForm({
    name: '',
    email: '',
    phone: '',
    subject: '',
    body: '',
});

const flash = computed(
    () => usePage().props.flash as { success?: string } | undefined,
);

function submit() {
    form.post(contactStore().url, {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
}
</script>

<template>
    <Head title="Contacto">
        <meta
            head-key="description"
            name="description"
            content="Contacta al equipo de Archivo de Chile. Envíanos tus consultas, sugerencias o comentarios."
        />
        <meta property="og:title" content="Contacto — Archivo de Chile" />
        <meta
            property="og:description"
            content="Contacta al equipo de Archivo de Chile."
        />
    </Head>

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-8 p-4">
            <div class="mx-auto w-full max-w-2xl">
                <div class="mb-8 text-center">
                    <div
                        class="mx-auto mb-4 flex size-14 items-center justify-center rounded-full bg-orange-100"
                    >
                        <Mail class="size-7 text-orange-600" />
                    </div>
                    <h1 class="text-2xl font-semibold tracking-tight">
                        Contáctanos
                    </h1>
                    <p class="mt-2 text-sm text-muted-foreground">
                        ¿Tienes dudas, sugerencias o quieres colaborar?
                        Escríbenos y te responderemos lo antes posible.
                    </p>
                </div>

                <!-- Success message -->
                <div
                    v-if="flash?.success"
                    class="mb-6 rounded-lg border border-green-200 bg-green-50 p-4 text-center text-sm text-green-800 dark:border-green-800 dark:bg-green-950 dark:text-green-200"
                >
                    {{ flash.success }}
                </div>

                <form class="space-y-5" @submit.prevent="submit">
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="name">
                                <User class="inline size-4" />
                                Nombre
                            </Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                type="text"
                                placeholder="Tu nombre"
                                required
                            />
                            <p
                                v-if="form.errors.name"
                                class="text-xs text-destructive"
                            >
                                {{ form.errors.name }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <Label for="email">
                                <Mail class="inline size-4" />
                                Correo electrónico
                            </Label>
                            <Input
                                id="email"
                                v-model="form.email"
                                type="email"
                                placeholder="tu@email.com"
                                required
                            />
                            <p
                                v-if="form.errors.email"
                                class="text-xs text-destructive"
                            >
                                {{ form.errors.email }}
                            </p>
                        </div>
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="phone">
                                <Phone class="inline size-4" />
                                Teléfono
                                <span class="text-muted-foreground"
                                    >(opcional)</span
                                >
                            </Label>
                            <Input
                                id="phone"
                                v-model="form.phone"
                                type="tel"
                                placeholder="+56 9 1234 5678"
                            />
                            <p
                                v-if="form.errors.phone"
                                class="text-xs text-destructive"
                            >
                                {{ form.errors.phone }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <Label for="subject">Asunto</Label>
                            <Input
                                id="subject"
                                v-model="form.subject"
                                type="text"
                                placeholder="¿Sobre qué quieres escribirnos?"
                                required
                            />
                            <p
                                v-if="form.errors.subject"
                                class="text-xs text-destructive"
                            >
                                {{ form.errors.subject }}
                            </p>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label for="body">Mensaje</Label>
                        <textarea
                            id="body"
                            v-model="form.body"
                            rows="6"
                            placeholder="Escribe tu mensaje aquí..."
                            required
                            class="border-input bg-background ring-offset-background placeholder:text-muted-foreground focus-visible:ring-ring flex min-h-[80px] w-full rounded-md border px-3 py-2 text-sm focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                        />
                        <p
                            v-if="form.errors.body"
                            class="text-xs text-destructive"
                        >
                            {{ form.errors.body }}
                        </p>
                    </div>

                    <Button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full"
                    >
                        <Send class="mr-2 size-4" />
                        {{ form.processing ? 'Enviando...' : 'Enviar mensaje' }}
                    </Button>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
