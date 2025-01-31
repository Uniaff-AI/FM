<template>
    <div @keydown.esc="closeOverlays" tabindex="-1" class="min-w-[320px]">
        <!-- Существующие UI-компоненты -->
        <Alert />
        <ToasterWrapper />
        <CookieDisclaimer />
        <RemoteUploadProgress />

        <!-- Показать спиннер до загрузки переводов -->
        <Spinner v-if="!isLoaded" />

        <!-- Показать предупреждающую панель при ограничении функциональности пользователя -->
        <RestrictionWarningBar />

        <div :class="{'lg:flex': isSidebarNavigation}">
            <SidebarNavigation v-if="isSidebarNavigation" />
            <router-view v-if="isLoaded" />
        </div>

        <!-- Фон под всплывающими окнами -->
        <Vignette />

        <!-- Компонент Social Chat отображается только на указанных страницах -->
        <SocialChat
            v-if="isMainPage"
            icon
            :attendants="attendants"
        >
            <template v-slot:header>
                Нажмите на одного из наших сотрудников ниже, чтобы начать чат в Telegram.
            </template>
            <template v-slot:button>
                <img
                    src="https://upload.wikimedia.org/wikipedia/commons/8/82/Telegram_logo.svg"
                    alt="Telegram Logo"
                    aria-hidden="true"
                />
            </template>
            <template v-slot:footer>
            </template>
        </SocialChat>
    </div>
</template>

<script>
import RestrictionWarningBar from './components/Subscription/RestrictionWarningBar'
import RemoteUploadProgress from "./components/RemoteUpload/RemoteUploadProgress"
import ToasterWrapper from './components/Toaster/ToasterNotifications'
import SidebarNavigation from "./components/Sidebar/SidebarNavigation"
import CookieDisclaimer from './components/UI/Others/CookieDisclaimer'
import Spinner from './components/UI/Others/Spinner'
import Vignette from './components/UI/Others/Vignette'
import Alert from './components/Popups/Alert'
import { mapGetters } from 'vuex'
import { events } from './bus'

// Импортируем компонент SocialChat
import { SocialChat } from 'vue-social-chat'

export default {
    name: 'App',
    components: {
        RestrictionWarningBar,
        RemoteUploadProgress,
        SidebarNavigation,
        CookieDisclaimer,
        ToasterWrapper,
        Vignette,
        Spinner,
        Alert,
        SocialChat, // Регистрация компонента SocialChat локально
    },
    data() {
        return {
            isLoaded: false,
            isSidebarNavigation: undefined,
            attendants: [ // Обновленные данные для attendants с Telegram
                {
                    app: 'telegram',
                    label: 'Саппорт',
                    name: 'Расул Хайруллин',
                    username: 'SadisticGG', // Telegram-юзернейм без @
                    avatar: {
                        src: 'https://avatars.githubusercontent.com/u/188985121?s=400&u=d01ff4c95a8ea86f2c31406fed61c8cfd19a02d7&v=4',
                        alt: 'Аватар Расул Хайруллина'
                    }
                },
                {
                    app: 'telegram',
                    label: 'Саппорт',
                    name: 'Максим Одинцов',
                    username: 'yanderoshi',
                    avatar: {
                        src: 'https://avatars.githubusercontent.com/u/59776723?v=4',
                        alt: 'Аватар Максима Одинцова'
                    }
                },
            ],
            isMainPage: false, // Новая переменная для отслеживания, является ли текущая страница основной
        }
    },
    computed: {
        ...mapGetters(['config', 'user']),
    },
    watch: {
        'config.defaultThemeMode': function() {
            this.handleDarkMode()
        },
        '$route'() {
            const currentPath = this.$router.currentRoute.fullPath;
            const app = document.getElementsByTagName('body')[0];

            // Проверяем, является ли текущий путь одним из указанных
            const mainPages = [
                '/platform/files',
                '/platform/recent-uploads',
                '/platform/my-shared-items',
                '/platform/trash',
                '/platform/team-folders',
                '/platform/shared-with-me',
                '/user/profile',
                '/user/settings/password',
                '/admin/dashboard',
                '/admin/users'
            ];

            // Устанавливаем флаг для отображения чата
            this.isMainPage = mainPages.includes(currentPath);

            // Установка фона через тему
            let section = currentPath.split('/')[1];
            if (['admin', 'user'].includes(section)) {
                app.classList.add('dark:bg-dark-background', 'bg-light-background');
            } else {
                app.classList.remove('dark:bg-dark-background', 'bg-light-background');
            }

            // Установка видимости боковой навигации
            this.isSidebarNavigation = ['admin', 'user', 'platform'].includes(section);
        }
    },
    methods: {
        closeOverlays() {
            events.$emit('popup:close');
            events.$emit('popover:close');
            this.$store.commit('CLOSE_NOTIFICATION_CENTER');
        },
        spotlightListener(e) {
            if ((e.key === 'k' && e.metaKey) || (e.key === 'k' && e.ctrlKey)) {
                e.preventDefault();
                events.$emit('spotlight:show');
            }
        },
        handleDarkMode() {
            const app = document.getElementsByTagName('html')[0];
            const prefersDarkScheme = window.matchMedia('(prefers-color-scheme: dark)');

            if (this.config.defaultThemeMode === 'dark') {
                app.classList.add('dark');
                this.$store.commit('UPDATE_DARK_MODE_STATUS', true);
            } else if (this.config.defaultThemeMode === 'light') {
                app.classList.remove('dark');
                this.$store.commit('UPDATE_DARK_MODE_STATUS', false);
            } else if (this.config.defaultThemeMode === 'system' && prefersDarkScheme.matches) {
                app.classList.add('dark');
                this.$store.commit('UPDATE_DARK_MODE_STATUS', true);
            } else if (this.config.defaultThemeMode === 'system' && !prefersDarkScheme.matches) {
                app.classList.remove('dark');
                this.$store.commit('UPDATE_DARK_MODE_STATUS', false);
            }
        },
    },
    beforeMount() {
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
            this.handleDarkMode();
        });

        // Коммит конфигурации
        this.$store.commit('INIT', {
            config: this.$root.$data.config,
        });

        // Редирект на мастер настройки
        if (this.$root.$data.config.installation === 'installation-needed') {
            this.isLoaded = true;

            if (window.location.pathname.split('/')[1] !== 'setup-wizard') {
                this.$router.push({ name: 'StatusCheck' });
            }
        } else {
            this.$store.dispatch('getLanguageTranslations', this.$root.$data.config.locale).then(() => {
                this.isLoaded = true;
            });
        }

        // Перейти на страницу входа, если домашняя страница отключена
        if (!this.$root.$data.config.allowHomepage && window.location.pathname === '/') {
            this.$router.push({ name: 'SignIn' });
        }
    },
    created() {
        if (this.$isWindows()) {
            document.body.classList.add('windows');
        }

        window.addEventListener('keydown', this.spotlightListener);
    },
    destroyed() {
        window.removeEventListener('keydown', this.spotlightListener);
    },
}
</script>

<style lang="scss">
@import url('https://fonts.googleapis.com/css2?family=Nunito:wght@200;300;400;600;700;800;900&display=swap');
@import '../sass/vuefilemanager/variables';
@import '../sass/vuefilemanager/mixins';

input:-webkit-autofill {
    transition-delay: 999999999999s;
}

[v-cloak],
[v-cloak] > * {
    display: none;
}

* {
    outline: 0;
    margin: 0;
    padding: 0;
    font-family: 'Nunito', sans-serif;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    box-sizing: border-box;
    -webkit-tap-highlight-color: rgba(0, 0, 0, 0);
    font-size: 16px;
    text-decoration: none;
    color: $text;
}

.vue-feather {
    path,
    circle,
    line,
    rect,
    polyline,
    ellipse,
    polygon {
        color: inherit;
    }
}

/* Dark mode */
.dark {
    * {
        color: $dark_mode_text_primary;
    }

    body,
    html {
        background: $dark_mode_background;
        color: $dark_mode_text_primary;

        img {
            opacity: 0.95;
        }
    }
}

/* Кастомизация стилей для vue-social-chat */
:root {
    --vsc-bg-header: #ffffff; /* Фон заголовка */
    --vsc-bg-footer: #f0f0f0; /* Фон нижней части */
    --vsc-text-color-header: #000000; /* Цвет текста заголовка */
    --vsc-text-color-footer: #000000; /* Цвет текста нижней части */
    --vsc-bg-button: #0088cc; /* Фон кнопки (синий для Telegram) */
    --vsc-text-color-button: #ffffff; /* Цвет текста кнопки */
    --vsc-outline-color: #000000; /* Цвет обводки */
    --vsc-border-color-bottom-header: transparent; /* Цвет нижней границы заголовка */
    --vsc-border-color-top-footer: #f3f3f3; /* Цвет верхней границы нижней части */

    /* Дополнительные переменные для текста */
    --vsc-text-color: #000000; /* Общий цвет текста */
    --vsc-message-text-color: #000000; /* Цвет текста сообщений */
    --vsc-input-text-color: #000000; /* Цвет текста в поле ввода */
}

/* Гарантируем, что все тексты внутри vue-social-chat черные */
.vue-social-chat * {
    color: #000000 !important;
}

/* Центрирование кнопки закрытия в vue-social-chat */
.vue-social-chat .vsc-close-button {
    position: absolute;
    top: 50%;
    right: 16px; /* Отступ справа */
    transform: translateY(-50%);
    display: flex;
    align-items: center;
    justify-content: center;
    width: 24px; /* Настройте размер по необходимости */
    height: 24px;
    cursor: pointer;
}

/* Дополнительные стили для заголовка, если необходимо */
.vue-social-chat .vsc-header {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
}

/* Если крестик всё ещё смещен, попробуйте добавить следующие стили */
.vue-social-chat .vsc-close-button svg {
    width: 100%;
    height: 100%;
}
</style>
