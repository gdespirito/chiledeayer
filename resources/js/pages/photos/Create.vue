<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Upload } from 'lucide-vue-next';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import { create as photosCreate, store as photosStore } from '@/routes/photos';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Fotos',
        href: '/photos',
    },
    {
        title: 'Subir foto',
        href: photosCreate(),
    },
];

const form = useForm<{
    photo: File | null;
    description: string;
    year_from: number | string;
    year_to: number | string;
    date_precision: 'exact' | 'year' | 'decade' | 'circa';
    place_id: number | null;
    source_credit: string;
    tags: string;
}>({
    photo: null,
    description: '',
    year_from: '',
    year_to: '',
    date_precision: 'year',
    place_id: null,
    source_credit: '',
    tags: '',
});

function onFileChange(event: Event): void {
    const target = event.target as HTMLInputElement;

    if (target.files && target.files.length > 0) {
        form.photo = target.files[0];
    }
}

function submit(): void {
    form.post(photosStore.url(), {
        forceFormData: true,
    });
}
</script>

<template>
    <Head title="Subir foto" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex w-full max-w-2xl flex-col gap-6 p-4">
            <h1 class="text-2xl font-semibold tracking-tight">Subir foto</h1>

            <form class="space-y-6" @submit.prevent="submit">
                <!-- Photo file -->
                <div class="grid gap-2">
                    <Label for="photo">Foto</Label>
                    <div
                        class="relative flex min-h-32 cursor-pointer items-center justify-center rounded-md border border-dashed border-input bg-background transition-colors hover:bg-accent/50 dark:bg-input/30 dark:hover:bg-input/50"
                    >
                        <input
                            id="photo"
                            type="file"
                            accept="image/*"
                            class="absolute inset-0 cursor-pointer opacity-0"
                            @change="onFileChange"
                        />
                        <div
                            class="flex flex-col items-center gap-2 text-sm text-muted-foreground"
                        >
                            <Upload class="size-8" />
                            <span v-if="form.photo">{{ form.photo.name }}</span>
                            <span v-else>Haz clic o arrastra una imagen</span>
                        </div>
                    </div>
                    <InputError :message="form.errors.photo" />
                </div>

                <!-- Description -->
                <div class="grid gap-2">
                    <Label for="description">Descripción</Label>
                    <textarea
                        id="description"
                        v-model="form.description"
                        class="min-h-24 w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs transition-[color,box-shadow] outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 aria-invalid:border-destructive aria-invalid:ring-destructive/20 dark:bg-input/30 dark:aria-invalid:ring-destructive/40"
                        placeholder="Describe la fotografía..."
                        required
                    />
                    <InputError :message="form.errors.description" />
                </div>

                <!-- Year range -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="grid gap-2">
                        <Label for="year_from">Año desde</Label>
                        <Input
                            id="year_from"
                            v-model="form.year_from"
                            type="number"
                            placeholder="1940"
                            min="1800"
                            max="2030"
                            required
                        />
                        <InputError :message="form.errors.year_from" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="year_to"
                            >Año hasta
                            <span class="text-muted-foreground"
                                >(opcional)</span
                            ></Label
                        >
                        <Input
                            id="year_to"
                            v-model="form.year_to"
                            type="number"
                            placeholder="1950"
                            min="1800"
                            max="2030"
                        />
                        <InputError :message="form.errors.year_to" />
                    </div>
                </div>

                <!-- Date precision -->
                <div class="grid gap-2">
                    <Label for="date_precision">Precisión de la fecha</Label>
                    <Select v-model="form.date_precision">
                        <SelectTrigger class="w-full">
                            <SelectValue placeholder="Seleccionar precisión" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="exact">Fecha exacta</SelectItem>
                            <SelectItem value="year">Año</SelectItem>
                            <SelectItem value="decade">Década</SelectItem>
                            <SelectItem value="circa"
                                >Aproximada (~)</SelectItem
                            >
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.date_precision" />
                </div>

                <!-- Source credit -->
                <div class="grid gap-2">
                    <Label for="source_credit"
                        >Fuente / Crédito
                        <span class="text-muted-foreground"
                            >(opcional)</span
                        ></Label
                    >
                    <Input
                        id="source_credit"
                        v-model="form.source_credit"
                        type="text"
                        placeholder="Ej: Archivo Nacional, colección familiar..."
                    />
                    <InputError :message="form.errors.source_credit" />
                </div>

                <!-- Tags -->
                <div class="grid gap-2">
                    <Label for="tags"
                        >Etiquetas
                        <span class="text-muted-foreground"
                            >(separadas por coma)</span
                        ></Label
                    >
                    <Input
                        id="tags"
                        v-model="form.tags"
                        type="text"
                        placeholder="Ej: santiago, plaza de armas, 1940"
                    />
                    <InputError :message="form.errors.tags" />
                </div>

                <!-- Submit -->
                <div class="flex items-center gap-4">
                    <Button type="submit" :disabled="form.processing">
                        Subir foto
                    </Button>
                    <p
                        v-if="form.processing"
                        class="text-sm text-muted-foreground"
                    >
                        Subiendo...
                    </p>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
