<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppHeader from '@/components/AppHeader.vue';
import AppShell from '@/components/AppShell.vue';
import type { BreadcrumbItem } from '@/types';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <AppShell class="flex-col">
        <AppHeader :breadcrumbs="breadcrumbs" />
        <AppContent>
            <slot />
        </AppContent>
        <footer
            class="border-t border-sidebar-border/80 py-6 text-center text-sm text-muted-foreground"
        >
            <div>
                Hecho con ❤️ por
                <a
                    href="https://freshwork.dev"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="underline underline-offset-2 hover:text-foreground"
                >
                    Freshwork
                </a>
                ·
                <a
                    href="/contacto"
                    class="underline underline-offset-2 hover:text-foreground"
                >
                    Contacto
                </a>
            </div>
        </footer>
    </AppShell>
</template>
