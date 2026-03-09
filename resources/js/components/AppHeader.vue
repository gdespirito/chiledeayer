<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    Camera,
    Heart,
    Home,
    ImagePlus,
    Map,
    MapPin,
    Menu,
    Search,
    Trophy,
} from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    NavigationMenu,
    NavigationMenuItem,
    NavigationMenuList,
    navigationMenuTriggerStyle,
} from '@/components/ui/navigation-menu';
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';
import UserMenuContent from '@/components/UserMenuContent.vue';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { getInitials } from '@/composables/useInitials';
import { home, leaderboard, login, map, register, search } from '@/routes';
import { index as photosIndex, create as photosCreate } from '@/routes/photos';
import { index as placesIndex } from '@/routes/places';
import type { BreadcrumbItem, NavItem } from '@/types';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

const props = withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const page = usePage();
const auth = computed(() => page.props.auth);
const { isCurrentUrl, whenCurrentUrl } = useCurrentUrl();

const activeItemStyles =
    'text-neutral-900 dark:bg-neutral-800 dark:text-neutral-100';

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
    <div>
        <div class="border-b border-sidebar-border/80">
            <div class="mx-auto flex h-16 items-center px-4 md:max-w-7xl">
                <!-- Mobile Menu -->
                <div class="lg:hidden">
                    <Sheet>
                        <SheetTrigger :as-child="true">
                            <Button
                                variant="ghost"
                                size="icon"
                                class="mr-2 h-9 w-9"
                            >
                                <Menu class="h-5 w-5" />
                            </Button>
                        </SheetTrigger>
                        <SheetContent side="left" class="w-[300px] p-6">
                            <SheetTitle class="sr-only"
                                >Menu de navegacion</SheetTitle
                            >
                            <SheetHeader class="flex justify-start text-left">
                                <AppLogoIcon
                                    class="size-6 text-black dark:text-white"
                                />
                            </SheetHeader>
                            <div
                                class="flex h-full flex-1 flex-col justify-between space-y-4 py-6"
                            >
                                <nav class="-mx-3 space-y-1">
                                    <Link
                                        v-for="item in [
                                            ...mainNavItems,
                                            ...authNavItems,
                                        ]"
                                        :key="item.title"
                                        :href="item.href"
                                        class="flex items-center gap-x-3 rounded-lg px-3 py-2 text-sm font-medium hover:bg-accent"
                                        :class="
                                            whenCurrentUrl(
                                                item.href,
                                                activeItemStyles,
                                            )
                                        "
                                    >
                                        <component
                                            v-if="item.icon"
                                            :is="item.icon"
                                            class="h-5 w-5"
                                        />
                                        {{ item.title }}
                                    </Link>
                                </nav>

                                <!-- Guest mobile links -->
                                <div
                                    v-if="!auth?.user"
                                    class="-mx-3 space-y-1 border-t pt-4"
                                >
                                    <Link
                                        href="/contribuir"
                                        class="flex items-center gap-x-3 rounded-lg px-3 py-2 text-sm font-medium text-amber-700 hover:bg-amber-50 dark:text-amber-400 dark:hover:bg-amber-950/30"
                                    >
                                        <Heart class="h-5 w-5" />
                                        Contribuir
                                    </Link>
                                    <Link
                                        :href="login().url"
                                        class="flex items-center gap-x-3 rounded-lg px-3 py-2 text-sm font-medium hover:bg-accent"
                                    >
                                        Iniciar sesión
                                    </Link>
                                    <Link
                                        :href="register().url"
                                        class="flex items-center gap-x-3 rounded-lg px-3 py-2 text-sm font-medium hover:bg-accent"
                                    >
                                        Registrarse
                                    </Link>
                                </div>
                            </div>
                        </SheetContent>
                    </Sheet>
                </div>

                <Link :href="home()" class="flex items-center gap-x-2">
                    <AppLogo />
                </Link>

                <!-- Desktop Menu -->
                <div class="hidden h-full lg:flex lg:flex-1">
                    <NavigationMenu class="ml-10 flex h-full items-stretch">
                        <NavigationMenuList
                            class="flex h-full items-stretch space-x-2"
                        >
                            <NavigationMenuItem
                                v-for="(item, index) in mainNavItems"
                                :key="index"
                                class="relative flex h-full items-center"
                            >
                                <Link
                                    :class="[
                                        navigationMenuTriggerStyle(),
                                        whenCurrentUrl(
                                            item.href,
                                            activeItemStyles,
                                        ),
                                        'h-9 cursor-pointer px-3',
                                    ]"
                                    :href="item.href"
                                >
                                    <component
                                        v-if="item.icon"
                                        :is="item.icon"
                                        class="mr-2 h-4 w-4"
                                    />
                                    {{ item.title }}
                                </Link>
                                <div
                                    v-if="isCurrentUrl(item.href)"
                                    class="absolute bottom-0 left-0 h-0.5 w-full translate-y-px bg-black dark:bg-white"
                                ></div>
                            </NavigationMenuItem>
                        </NavigationMenuList>
                    </NavigationMenu>
                </div>

                <div class="ml-auto flex items-center space-x-2">
                    <div class="relative flex items-center space-x-1">
                        <Link :href="search().url">
                            <Button
                                variant="ghost"
                                size="icon"
                                class="group h-9 w-9 cursor-pointer"
                            >
                                <Search
                                    class="size-5 opacity-80 group-hover:opacity-100"
                                />
                            </Button>
                        </Link>
                    </div>

                    <!-- Authenticated user: upload button + avatar dropdown -->
                    <template v-if="auth?.user">
                        <Link
                            :href="photosCreate().url"
                            class="hidden lg:block"
                        >
                            <Button
                                class="cursor-pointer bg-amber-500 text-white hover:bg-amber-600 dark:bg-amber-600 dark:hover:bg-amber-700"
                            >
                                <ImagePlus class="mr-2 h-4 w-4" />
                                Subir foto
                            </Button>
                        </Link>

                        <DropdownMenu>
                            <DropdownMenuTrigger :as-child="true">
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    class="relative size-10 w-auto rounded-full p-1 focus-within:ring-2 focus-within:ring-primary"
                                >
                                    <Avatar
                                        class="size-8 overflow-hidden rounded-full"
                                    >
                                        <AvatarImage
                                            v-if="auth.user.avatar"
                                            :src="auth.user.avatar"
                                            :alt="auth.user.name"
                                        />
                                        <AvatarFallback
                                            class="rounded-lg bg-neutral-200 font-semibold text-black dark:bg-neutral-700 dark:text-white"
                                        >
                                            {{ getInitials(auth.user?.name) }}
                                        </AvatarFallback>
                                    </Avatar>
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end" class="w-56">
                                <UserMenuContent :user="auth.user" />
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </template>

                    <!-- Guest user: contribute + login + register buttons -->
                    <template v-else>
                        <Link href="/contribuir" class="hidden lg:block">
                            <Button
                                variant="outline"
                                class="cursor-pointer border-amber-500/50 text-amber-700 hover:bg-amber-50 dark:border-amber-400/50 dark:text-amber-400 dark:hover:bg-amber-950/30"
                            >
                                <Heart class="mr-2 h-4 w-4" />
                                Contribuir
                            </Button>
                        </Link>
                        <Link :href="login().url">
                            <Button variant="ghost" class="cursor-pointer">
                                Iniciar sesión
                            </Button>
                        </Link>
                        <Link :href="register().url">
                            <Button class="cursor-pointer">
                                Registrarse
                            </Button>
                        </Link>
                    </template>
                </div>
            </div>
        </div>

        <div
            v-if="props.breadcrumbs.length > 1"
            class="flex w-full border-b border-sidebar-border/70"
        >
            <div
                class="mx-auto flex h-12 w-full items-center justify-start px-4 text-neutral-500 md:max-w-7xl"
            >
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
            </div>
        </div>
    </div>
</template>
