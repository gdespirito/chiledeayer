<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { Camera, Home, ImagePlus, Map, MapPin, Trophy } from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { home, leaderboard, map } from '@/routes';
import { index as photosIndex, create as photosCreate } from '@/routes/photos';
import { index as placesIndex } from '@/routes/places';
import type { NavItem } from '@/types';

const page = usePage();
const auth = computed(() => page.props.auth);

const mainNavItems: NavItem[] = [
    {
        title: 'Inicio',
        href: home(),
        icon: Home,
    },
    {
        title: 'Fotos',
        href: photosIndex(),
        icon: Camera,
    },
    {
        title: 'Lugares',
        href: placesIndex(),
        icon: MapPin,
    },
    {
        title: 'Mapa',
        href: map(),
        icon: Map,
    },
    {
        title: 'Tabla de Honor',
        href: leaderboard(),
        icon: Trophy,
    },
];

const authNavItems = computed<NavItem[]>(() => {
    if (!auth.value?.user) {
        return [];
    }

    return [
        {
            title: 'Subir Foto',
            href: photosCreate(),
            icon: ImagePlus,
        },
    ];
});
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="home()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
            <NavMain
                v-if="authNavItems.length > 0"
                :items="authNavItems"
                label="Contribuir"
            />
        </SidebarContent>

        <SidebarFooter v-if="$page.props.auth.user">
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
