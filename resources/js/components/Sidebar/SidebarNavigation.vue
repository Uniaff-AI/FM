<!-- SidebarNavigation.vue -->
<template>
    <nav
        v-if="isVisibleNavigationBars"
        class="menu-bar z-10 hidden w-16 flex-none select-none bg-light-background pt-7 dark:bg-dark-foreground lg:grid xl:w-20"
    >
        <!-- Navigation -->
        <div v-if="user" class="mb-auto text-center">
            <MemberAvatar class="mx-auto" :size="44" :is-border="false" :member="user" />

            <!-- Usage -->
            <div
                v-if="config.subscriptionType === 'metered' && user.data.meta.usages"
                class="mt-2.5 text-center leading-3"
            >
                <b class="text-theme block text-xs font-bold leading-3">
                    {{ user.data.meta.usages.costEstimate }}
                </b>
                <span class="text-xs text-gray-500">
                    {{ $t('usage') }}
                </span>
            </div>

            <!-- Notification Bell -->
            <div class="mt-2 relative">
                <NotificationBell @click.native="$store.commit('TOGGLE_NOTIFICATION_CENTER')" class="hover:bg-light-300 dark:hover:bg-4x-dark-foreground" />
            </div>

            <NotificationCenter v-if="isVisibleNotificationCenter" />

            <!-- Navigation Links -->
            <div class="mt-6">
                <router-link
                    v-for="(item, i) in navigation"
                    :to="{ name: item.route }"
                    :key="i"
                    :title="item.title"
                    :class="[{ 'router-link-active': isSection(item.section) }, item.icon]"
                    class="mb-1.5 block"
                >
                    <div
                        class="button-icon text-theme inline-block cursor-pointer rounded-xl p-3 hover:bg-light-300 dark:hover:bg-4x-dark-foreground"
                    >
                        <!-- Dynamic Icon Rendering -->
                        <component
                            :is="getIconComponent(item.icon)"
                            size="20"
                            class="vue-feather"
                        />
                    </div>
                </router-link>
            </div>

            <!-- Toggle Dark/Light mode -->
            <div @click="$store.dispatch('toggleThemeMode')" :title="$t('dark_mode_toggle')" class="mt-6 block">
                <div
                    class="button-icon inline-block cursor-pointer rounded-xl p-3 hover:bg-light-300 dark:hover:bg-4x-dark-foreground"
                >
                    <component
                        :is="isDarkMode ? 'SunIcon' : 'MoonIcon'"
                        size="20"
                        class="vue-feather"
                    />
                </div>
            </div>
        </div>

        <!-- Logout -->
        <div class="mt-auto text-center">
            <div
                @click="$store.dispatch('logOut')"
                :title="$t('logout')"
                class="button-icon inline-block cursor-pointer rounded-xl p-3 hover:bg-light-300 dark:hover:bg-4x-dark-foreground"
            >
                <PowerIcon size="20" />
            </div>
        </div>
    </nav>
</template>

<script>
import MemberAvatar from '../UI/Others/MemberAvatar';
import { mapGetters } from 'vuex';
import {
    HardDriveIcon,
    MoonIcon,
    PowerIcon,
    SettingsIcon,
    SunIcon,
    UserIcon,
} from 'vue-feather-icons';
import NotificationCenter from "../Notifications/NotificationCenter";
import NotificationBell from "../Notifications/Components/NotificationBell";

export default {
    name: 'SidebarNavigation',
    components: {
        NotificationBell,
        NotificationCenter,
        HardDriveIcon,
        SettingsIcon,
        UserIcon,
        MemberAvatar,
        PowerIcon,
        MoonIcon,
        SunIcon,
    },
    computed: {
        ...mapGetters(['isVisibleNavigationBars', 'isDarkMode', 'config', 'user', 'isVisibleNotificationCenter']),

        /**
         * Проверка, является ли пользователь администратором
         */
        isAdmin() {
            return this.user && this.user.data.attributes.role === 'admin';
        },

        /**
         * Проверка, является ли пользователь помощником
         */
        isHelper() {
            return this.user && this.user.data.attributes.role === 'helper';
        },

        /**
         * Проверка, имеет ли пользователь административные права (admin или helper)
         */
        hasAdminPrivileges() {
            return this.isAdmin || this.isHelper;
        },

        /**
         * Определение навигационных пунктов на основе роли пользователя
         */
        navigation() {
            // Base navigation for all users
            const baseNavigation = [
                {
                    route: 'Profile',
                    section: 'User',
                    title: this.$t('locations.profile'),
                    icon: 'user',
                },
            ];

            // Conditionally include 'Files' only for admins
            if (this.isAdmin) {
                baseNavigation.unshift({
                    route: 'Files',
                    section: 'Platform',
                    title: this.$t('locations.home'),
                    icon: 'home',
                });
            }

            // Admin-specific navigation items
            const adminNavigation = [
                {
                    route: 'Dashboard',
                    section: 'Admin',
                    title: this.$t('locations.settings'),
                    icon: 'settings',
                },
                // Добавьте другие админские маршруты по мере необходимости
            ];

            // Include admin navigation items if the user has admin privileges
            if (this.hasAdminPrivileges) {
                return [...baseNavigation, ...adminNavigation];
            }

            return baseNavigation;
        },
    },
    data() {
        return {
            isNotificationCenter: false,
        };
    },
    methods: {
        isSection(section) {
            return this.$route.matched[0].name === section;
        },

        /**
         * Возвращает соответствующий компонент иконки на основе имени иконки
         * @param {String} iconName - Имя иконки
         * @returns {Component} - Компонент иконки
         */
        getIconComponent(iconName) {
            const iconsMap = {
                'home': 'HardDriveIcon',
                'settings': 'SettingsIcon',
                'user': 'UserIcon',
                // Добавьте другие иконки по мере необходимости
            };
            return iconsMap[iconName] || 'HardDriveIcon'; // Default icon
        },
    },
    mounted() {
        this.$store.dispatch('getAppData');
    },
};
</script>

<style scoped lang="scss">
@import '../../../sass/vuefilemanager/variables';

.menu-bar {
    //background: linear-gradient(180deg, rgba(246, 245, 241, 0.8) 0%, rgba(243, 244, 246, 0.8) 100%);
}

.router-link-active {
    &.home .button-icon {
        path,
        line,
        polyline,
        rect,
        circle {
            color: inherit;
        }
    }

    &.trash .button-icon {
        background: rgba($red, 0.1);

        path,
        line,
        polyline,
        rect,
        circle {
            stroke: $red;
        }
    }

    &.settings .button-icon {
        background: rgba($purple, 0.1);

        path,
        line,
        polyline,
        rect,
        circle {
            stroke: $purple;
        }
    }

    &.user .button-icon {
        background: rgba($pink, 0.1);

        path,
        line,
        polyline,
        rect,
        circle {
            stroke: $pink;
        }
    }
}
</style>
