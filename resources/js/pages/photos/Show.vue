<script setup lang="ts">
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import {
    Calendar,
    Camera,
    ImagePlus,
    MapPin,
    MessageCircle,
    Tag as TagIcon,
    ThumbsDown,
    ThumbsUp,
    Trash2,
    User,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { login, register } from '@/routes';
import { destroy as commentsDestroy } from '@/routes/comments';
import { index as photosIndex, show as photosShow } from '@/routes/photos';
import { store as commentsStore } from '@/routes/photos/comments';
import { store as comparisonsStore } from '@/routes/photos/comparisons';
import { store as voteStore } from '@/routes/photos/vote';
import type { BreadcrumbItem, Comment, ComparisonPhoto, Photo } from '@/types';

type Props = {
    photo: { data: Photo };
    comments: { data: Comment[] };
};

const props = defineProps<Props>();
const photo = computed(() => props.photo.data);
const comments = computed(() => props.comments.data);

const page = usePage();
const auth = computed(() => page.props.auth);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Fotos',
        href: photosIndex(),
    },
    {
        title:
            photo.value.description.length > 40
                ? photo.value.description.substring(0, 40) + '...'
                : photo.value.description,
        href: photosShow(photo.value.id),
    },
];

const showComparisonForm = ref(false);

const comparisonForm = useForm<{
    photo: File | null;
    description: string;
    taken_at: string;
}>({
    photo: null,
    description: '',
    taken_at: '',
});

function onComparisonFileChange(event: Event): void {
    const target = event.target as HTMLInputElement;

    if (target.files && target.files.length > 0) {
        comparisonForm.photo = target.files[0];
    }
}

function submitComparison(): void {
    comparisonForm.post(comparisonsStore.url(photo.value.id), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            showComparisonForm.value = false;
            comparisonForm.reset();
        },
    });
}

function getDisplayImage(photo: Photo): string | null {
    const medium = photo.files.find((f) => f.variant === 'medium');
    const original = photo.files.find((f) => f.variant === 'original');

    return medium?.url ?? original?.url ?? null;
}

function getComparisonImage(comparison: ComparisonPhoto): string | null {
    return comparison.medium_url ?? comparison.original_url ?? null;
}

function formatDateRange(photo: Photo): string {
    switch (photo.date_precision) {
        case 'exact':
            return photo.year_to && photo.year_to !== photo.year_from
                ? `${photo.year_from}--${photo.year_to}`
                : `${photo.year_from}`;
        case 'year':
            return photo.year_to && photo.year_to !== photo.year_from
                ? `${photo.year_from}--${photo.year_to}`
                : `${photo.year_from}`;
        case 'decade':
            return `Década de ${photo.year_from}`;
        case 'circa':
            return `~${photo.year_from}`;
        default:
            return `${photo.year_from}`;
    }
}

function formatPrecisionLabel(precision: Photo['date_precision']): string {
    switch (precision) {
        case 'exact':
            return 'Fecha exacta';
        case 'year':
            return 'Ano';
        case 'decade':
            return 'Década';
        case 'circa':
            return 'Aproximada';
        default:
            return precision;
    }
}

const yearDisplay = computed(() => formatDateRange(photo.value));

const ogTitle = computed(() => {
    const desc = photo.value.description;

    return desc.length > 70 ? desc.substring(0, 67) + '...' : desc;
});

const ogDescription = computed(
    () => `${photo.value.description} (${yearDisplay.value})`,
);

const ogImage = computed(() => {
    const medium = photo.value.files.find((f) => f.variant === 'medium');
    const original = photo.value.files.find((f) => f.variant === 'original');

    return medium?.url ?? original?.url ?? null;
});

const hasComparisons = computed(
    () => photo.value.comparisons && photo.value.comparisons.length > 0,
);

const userHasComparison = computed(() => {
    if (!auth.value?.user || !photo.value.comparisons) {
        return false;
    }

    return photo.value.comparisons.some(
        (c) => c.user.id === auth.value.user.id,
    );
});

// Voting
const isVoting = ref(false);

function vote(value: 1 | -1): void {
    isVoting.value = true;
    router.post(
        voteStore.url(photo.value.id),
        { value },
        {
            preserveScroll: true,
            onFinish: () => {
                isVoting.value = false;
            },
        },
    );
}

// Comment form
const commentForm = useForm<{ body: string }>({
    body: '',
});

function submitComment(): void {
    commentForm.post(commentsStore.url(photo.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            commentForm.reset();
        },
    });
}

function deleteComment(commentId: number): void {
    router.delete(commentsDestroy.url(commentId), {
        preserveScroll: true,
    });
}

// Format relative date for comments
function formatRelativeDate(dateString: string): string {
    const date = new Date(dateString);
    const now = new Date();
    const diffMs = now.getTime() - date.getTime();
    const diffSeconds = Math.floor(diffMs / 1000);
    const diffMinutes = Math.floor(diffSeconds / 60);
    const diffHours = Math.floor(diffMinutes / 60);
    const diffDays = Math.floor(diffHours / 24);

    if (diffDays > 30) {
        return new Intl.DateTimeFormat('es-CL', {
            day: 'numeric',
            month: 'long',
            year: 'numeric',
        }).format(date);
    }

    if (diffDays > 0) {
        return `hace ${diffDays} ${diffDays === 1 ? 'día' : 'días'}`;
    }

    if (diffHours > 0) {
        return `hace ${diffHours} ${diffHours === 1 ? 'hora' : 'horas'}`;
    }

    if (diffMinutes > 0) {
        return `hace ${diffMinutes} ${diffMinutes === 1 ? 'minuto' : 'minutos'}`;
    }

    return 'hace un momento';
}

// Get user initials for avatar
function getUserInitials(name: string): string {
    return name
        .split(' ')
        .map((n) => n[0])
        .join('')
        .toUpperCase()
        .substring(0, 2);
}
</script>

<template>
    <Head :title="ogTitle">
        <meta property="og:title" :content="ogTitle" />
        <meta property="og:description" :content="ogDescription" />
        <meta v-if="ogImage" property="og:image" :content="ogImage" />
        <meta property="og:type" content="article" />
    </Head>

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4">
            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Image -->
                <div class="lg:col-span-2">
                    <div
                        class="overflow-hidden rounded-xl border border-sidebar-border/70 bg-muted dark:border-sidebar-border"
                    >
                        <img
                            v-if="getDisplayImage(photo)"
                            :src="getDisplayImage(photo)!"
                            :alt="photo.description"
                            class="w-full object-contain"
                        />
                        <div
                            v-else
                            class="flex aspect-video items-center justify-center text-muted-foreground"
                        >
                            <span class="text-sm">
                                Imagen en procesamiento...
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Metadata -->
                <div class="space-y-6">
                    <!-- Description -->
                    <div>
                        <h1 class="text-xl font-semibold tracking-tight">
                            {{ photo.description }}
                        </h1>
                    </div>

                    <!-- Voting -->
                    <div
                        class="flex items-center gap-3 rounded-lg border border-sidebar-border/70 p-3 dark:border-sidebar-border"
                    >
                        <template v-if="auth?.user">
                            <Button
                                variant="ghost"
                                size="sm"
                                :class="[
                                    photo.user_vote === 1
                                        ? 'text-green-600 dark:text-green-400'
                                        : 'text-muted-foreground',
                                ]"
                                :disabled="isVoting"
                                @click="vote(1)"
                            >
                                <ThumbsUp class="size-4" />
                            </Button>
                            <span
                                class="min-w-8 text-center text-sm font-semibold"
                            >
                                {{ photo.score }}
                            </span>
                            <Button
                                variant="ghost"
                                size="sm"
                                :class="[
                                    photo.user_vote === -1
                                        ? 'text-red-600 dark:text-red-400'
                                        : 'text-muted-foreground',
                                ]"
                                :disabled="isVoting"
                                @click="vote(-1)"
                            >
                                <ThumbsDown class="size-4" />
                            </Button>
                        </template>
                        <template v-else>
                            <ThumbsUp class="size-4 text-muted-foreground/50" />
                            <span
                                class="min-w-8 text-center text-sm font-semibold"
                            >
                                {{ photo.score }}
                            </span>
                            <ThumbsDown
                                class="size-4 text-muted-foreground/50"
                            />
                            <Link
                                :href="login.url()"
                                class="ml-auto text-xs text-muted-foreground hover:underline"
                            >
                                Inicia sesión para votar
                            </Link>
                        </template>
                    </div>

                    <!-- Date -->
                    <div class="space-y-1">
                        <div
                            class="flex items-center gap-2 text-sm font-medium"
                        >
                            <Calendar class="size-4 text-muted-foreground" />
                            Fecha
                        </div>
                        <p class="text-sm text-muted-foreground">
                            {{ formatDateRange(photo) }}
                            <span class="text-xs">
                                ({{
                                    formatPrecisionLabel(photo.date_precision)
                                }})
                            </span>
                        </p>
                    </div>

                    <!-- Place -->
                    <div v-if="photo.place" class="space-y-1">
                        <div
                            class="flex items-center gap-2 text-sm font-medium"
                        >
                            <MapPin class="size-4 text-muted-foreground" />
                            Lugar
                        </div>
                        <p class="text-sm text-muted-foreground">
                            {{ photo.place.name }}
                            <template
                                v-if="photo.place.city || photo.place.region"
                            >
                                <br />
                                <span class="text-xs">
                                    {{
                                        [
                                            photo.place.city,
                                            photo.place.region,
                                            photo.place.country,
                                        ]
                                            .filter(Boolean)
                                            .join(', ')
                                    }}
                                </span>
                            </template>
                        </p>
                    </div>

                    <!-- Tags -->
                    <div v-if="photo.tags.length > 0" class="space-y-2">
                        <div
                            class="flex items-center gap-2 text-sm font-medium"
                        >
                            <TagIcon class="size-4 text-muted-foreground" />
                            Etiquetas
                        </div>
                        <div class="flex flex-wrap gap-1.5">
                            <Badge
                                v-for="tag in photo.tags"
                                :key="tag.id"
                                variant="secondary"
                            >
                                {{ tag.name }}
                            </Badge>
                        </div>
                    </div>

                    <!-- Source/Credit -->
                    <div v-if="photo.source_credit" class="space-y-1">
                        <div
                            class="flex items-center gap-2 text-sm font-medium"
                        >
                            <Camera class="size-4 text-muted-foreground" />
                            Fuente / Crédito
                        </div>
                        <p class="text-sm text-muted-foreground">
                            {{ photo.source_credit }}
                        </p>
                    </div>

                    <!-- Uploader -->
                    <div class="space-y-1">
                        <div
                            class="flex items-center gap-2 text-sm font-medium"
                        >
                            <User class="size-4 text-muted-foreground" />
                            Subida por
                        </div>
                        <p class="text-sm text-muted-foreground">
                            {{ photo.user.name }}
                        </p>
                    </div>

                    <!-- Upload comparison button (auth only) -->
                    <div
                        v-if="
                            auth?.user &&
                            !userHasComparison &&
                            !showComparisonForm
                        "
                    >
                        <Button
                            variant="outline"
                            class="w-full"
                            @click="showComparisonForm = true"
                        >
                            <ImagePlus class="mr-2 size-4" />
                            Subir foto del ahora
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Comparison upload form -->
            <div
                v-if="showComparisonForm"
                class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
            >
                <h2 class="mb-4 text-lg font-semibold">Subir foto del ahora</h2>
                <form class="space-y-4" @submit.prevent="submitComparison">
                    <div class="space-y-2">
                        <Label for="comparison-photo">Foto</Label>
                        <Input
                            id="comparison-photo"
                            type="file"
                            accept="image/*"
                            @change="onComparisonFileChange"
                        />
                        <InputError :message="comparisonForm.errors.photo" />
                    </div>

                    <div class="space-y-2">
                        <Label for="comparison-description"
                            >Descripcion (opcional)</Label
                        >
                        <Input
                            id="comparison-description"
                            v-model="comparisonForm.description"
                            type="text"
                            placeholder="Describe la foto actual..."
                        />
                        <InputError
                            :message="comparisonForm.errors.description"
                        />
                    </div>

                    <div class="space-y-2">
                        <Label for="comparison-taken-at"
                            >Fecha de la foto (opcional)</Label
                        >
                        <Input
                            id="comparison-taken-at"
                            v-model="comparisonForm.taken_at"
                            type="date"
                        />
                        <InputError :message="comparisonForm.errors.taken_at" />
                    </div>

                    <div class="flex gap-2">
                        <Button
                            type="submit"
                            :disabled="comparisonForm.processing"
                        >
                            Subir
                        </Button>
                        <Button
                            type="button"
                            variant="ghost"
                            @click="
                                showComparisonForm = false;
                                comparisonForm.reset();
                            "
                        >
                            Cancelar
                        </Button>
                    </div>
                </form>
            </div>

            <!-- Comparisons section (side by side) -->
            <div v-if="hasComparisons" class="space-y-4">
                <h2 class="text-lg font-semibold">Foto del ahora</h2>

                <div
                    v-for="comparison in photo.comparisons"
                    :key="comparison.id"
                    class="overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
                >
                    <div class="grid gap-0 md:grid-cols-2">
                        <!-- Historical photo -->
                        <div class="relative">
                            <div class="absolute top-2 left-2 z-10">
                                <Badge variant="secondary"> Antes </Badge>
                            </div>
                            <img
                                v-if="getDisplayImage(photo)"
                                :src="getDisplayImage(photo)!"
                                :alt="photo.description"
                                class="h-full w-full object-cover"
                            />
                        </div>

                        <!-- Comparison photo -->
                        <div class="relative">
                            <div class="absolute top-2 left-2 z-10">
                                <Badge>Ahora</Badge>
                            </div>
                            <img
                                v-if="getComparisonImage(comparison)"
                                :src="getComparisonImage(comparison)!"
                                :alt="
                                    comparison.description ?? 'Foto del ahora'
                                "
                                class="h-full w-full object-cover"
                            />
                            <div
                                v-else
                                class="flex aspect-video items-center justify-center bg-muted text-muted-foreground"
                            >
                                <span class="text-sm">
                                    Imagen en procesamiento...
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Comparison metadata -->
                    <div
                        class="flex items-center gap-4 border-t border-sidebar-border/70 px-4 py-3 text-sm text-muted-foreground dark:border-sidebar-border"
                    >
                        <span> Por {{ comparison.user.name }} </span>
                        <span v-if="comparison.taken_at">
                            {{ comparison.taken_at }}
                        </span>
                        <span v-if="comparison.description">
                            {{ comparison.description }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Guest contribution banner -->
            <Card v-if="!auth?.user" class="border-dashed">
                <CardHeader>
                    <CardTitle class="text-base">
                        ¿Quieres contribuir?
                    </CardTitle>
                    <CardDescription>
                        Inicia sesión para votar, comentar y subir fotos.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="flex gap-2">
                        <Button as-child>
                            <Link :href="login.url()"> Iniciar sesión </Link>
                        </Button>
                        <Button variant="outline" as-child>
                            <Link :href="register.url()"> Registrarse </Link>
                        </Button>
                    </div>
                </CardContent>
            </Card>

            <!-- Comments section -->
            <div class="space-y-6">
                <div class="flex items-center gap-2">
                    <MessageCircle class="size-5" />
                    <h2 class="text-lg font-semibold">
                        Comentarios
                        <span
                            v-if="comments.length > 0"
                            class="text-sm font-normal text-muted-foreground"
                        >
                            ({{ comments.length }})
                        </span>
                    </h2>
                </div>

                <!-- Comment form (authenticated users) -->
                <form
                    v-if="auth?.user"
                    class="space-y-3"
                    @submit.prevent="submitComment"
                >
                    <div class="flex gap-3">
                        <Avatar class="size-8 shrink-0">
                            <AvatarFallback class="text-xs">
                                {{ getUserInitials(auth.user.name) }}
                            </AvatarFallback>
                        </Avatar>
                        <div class="flex-1 space-y-2">
                            <textarea
                                v-model="commentForm.body"
                                class="flex min-h-20 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                                placeholder="Escribe un comentario..."
                            />
                            <InputError :message="commentForm.errors.body" />
                            <div class="flex justify-end">
                                <Button
                                    type="submit"
                                    size="sm"
                                    :disabled="
                                        commentForm.processing ||
                                        !commentForm.body.trim()
                                    "
                                >
                                    Comentar
                                </Button>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Guest comment prompt -->
                <div
                    v-else
                    class="rounded-lg border border-dashed border-sidebar-border/70 p-4 text-center text-sm text-muted-foreground dark:border-sidebar-border"
                >
                    <Link
                        :href="login.url()"
                        class="font-medium hover:underline"
                    >
                        Inicia sesión para comentar
                    </Link>
                </div>

                <!-- Comments list -->
                <div v-if="comments.length > 0" class="space-y-4">
                    <div
                        v-for="comment in comments"
                        :key="comment.id"
                        class="flex gap-3"
                    >
                        <Avatar class="size-8 shrink-0">
                            <AvatarFallback class="text-xs">
                                {{ getUserInitials(comment.user.name) }}
                            </AvatarFallback>
                        </Avatar>
                        <div class="flex-1 space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-medium">
                                    {{ comment.user.name }}
                                </span>
                                <span class="text-xs text-muted-foreground">
                                    {{ formatRelativeDate(comment.created_at) }}
                                </span>
                                <Button
                                    v-if="
                                        auth?.user &&
                                        auth.user.id === comment.user.id
                                    "
                                    variant="ghost"
                                    size="sm"
                                    class="ml-auto h-6 w-6 p-0 text-muted-foreground hover:text-destructive"
                                    @click="deleteComment(comment.id)"
                                >
                                    <Trash2 class="size-3" />
                                </Button>
                            </div>
                            <p class="text-sm text-muted-foreground">
                                {{ comment.body }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Empty state -->
                <p
                    v-else
                    class="py-4 text-center text-sm text-muted-foreground"
                >
                    No hay comentarios aún. Sé el primero en comentar.
                </p>
            </div>
        </div>
    </AppLayout>
</template>
