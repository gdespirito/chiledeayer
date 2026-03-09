<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { ChevronDown, ChevronUp, MapPin } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { login } from '@/routes';
import { show as photosShow } from '@/routes/photos';
import { store as voteStore } from '@/routes/photos/vote';
import type { Photo } from '@/types';

type Props = {
    photo: Photo;
};

const props = defineProps<Props>();

const page = usePage();
const isAuthenticated = computed(() => !!page.props.auth?.user);
const isVoting = ref(false);

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

function vote(value: 1 | -1): void {
    if (!isAuthenticated.value) {
        router.visit(login().url);
        return;
    }

    isVoting.value = true;
    router.post(
        voteStore.url(props.photo.id),
        { value },
        {
            preserveScroll: true,
            onFinish: () => {
                isVoting.value = false;
            },
        },
    );
}
</script>

<template>
    <div
        class="group overflow-hidden rounded-xl border border-sidebar-border/70 bg-card shadow-sm transition-shadow hover:shadow-md dark:border-sidebar-border"
    >
        <Link :href="photosShow(props.photo.id)" class="block">
            <div class="relative aspect-[4/3] overflow-hidden bg-muted">
                <img
                    v-if="getThumbnail(props.photo)"
                    :src="getThumbnail(props.photo)!"
                    :alt="props.photo.title"
                    class="size-full object-cover transition-transform duration-300 group-hover:scale-105"
                />
                <div
                    v-else
                    class="flex size-full items-center justify-center text-muted-foreground"
                >
                    <span class="text-sm">Sin imagen</span>
                </div>
            </div>
            <div class="space-y-1.5 px-4 pt-4">
                <p class="line-clamp-2 text-sm font-medium">
                    {{ props.photo.title }}
                </p>
                <div
                    class="flex items-center gap-3 text-xs text-muted-foreground"
                >
                    <span>{{ formatDateRange(props.photo) }}</span>
                    <span
                        v-if="props.photo.place"
                        class="flex items-center gap-1"
                    >
                        <MapPin class="size-3" />
                        {{ props.photo.place.name }}
                    </span>
                </div>
            </div>
        </Link>
        <div class="flex items-center gap-1 px-4 pt-2 pb-3">
            <button
                type="button"
                class="flex items-center gap-0.5 rounded-md px-1.5 py-1 text-xs transition-colors hover:bg-stone-100 dark:hover:bg-stone-800"
                :class="
                    props.photo.user_vote === 1
                        ? 'text-amber-600 dark:text-amber-400'
                        : 'text-muted-foreground'
                "
                :disabled="isVoting"
                @click="vote(1)"
            >
                <ChevronUp class="size-4" />
            </button>
            <span
                class="min-w-[1.25rem] text-center text-xs font-medium"
                :class="
                    props.photo.score > 0
                        ? 'text-amber-600 dark:text-amber-400'
                        : props.photo.score < 0
                          ? 'text-red-500'
                          : 'text-muted-foreground'
                "
            >
                {{ props.photo.score }}
            </span>
            <button
                type="button"
                class="flex items-center gap-0.5 rounded-md px-1.5 py-1 text-xs transition-colors hover:bg-stone-100 dark:hover:bg-stone-800"
                :class="
                    props.photo.user_vote === -1
                        ? 'text-red-500'
                        : 'text-muted-foreground'
                "
                :disabled="isVoting"
                @click="vote(-1)"
            >
                <ChevronDown class="size-4" />
            </button>
        </div>
    </div>
</template>
