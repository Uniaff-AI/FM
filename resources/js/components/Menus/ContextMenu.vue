<!-- ContextMenu.vue -->
<template>
    <div
        v-show="isVisible"
        :style="{ top: positionY + 'px', left: positionX + 'px' }"
        @click="closeAndResetContextMenu"
        class="fixed z-20 w-60 select-none overflow-hidden rounded-xl bg-white shadow-lg dark:bg-2x-dark-foreground"
        ref="contextmenu"
    >
        <div class="w-full">
            <!-- Показать контекстное меню для пустого выбора -->
            <slot name="empty-select" v-if="!item" />

            <!-- Показать контекстное меню для одиночного выбора -->
            <slot name="single-select" v-if="isSingleSelectContextMenu" />

            <!-- Показать контекстное меню для множественного выбора -->
            <slot name="multiple-select" v-else />
        </div>
    </div>
</template>

<script>
import { events } from '../../bus'
import { mapGetters } from 'vuex'

export default {
    name: 'ContextMenu',
    computed: {
        ...mapGetters(['clipboard', 'user']),

        /**
         * Проверка, является ли пользователь администратором
         */
        isAdmin() {
            return this.user && this.user.data.attributes.role === 'admin';
        },

        /**
         * Определение, для какого типа выбора отображать контекстное меню
         */
        isSingleSelectContextMenu() {
            // Логика отображения для нескольких элементов
            if (this.clipboard.length > 1 && this.clipboard.includes(this.item)) return false;

            // Логика отображения для одного элемента
            if (this.clipboard.length < 2 || !this.clipboard.includes(this.item)) return true;
        },
    },
    data() {
        return {
            item: undefined,
            isVisible: false,
            positionX: 0,
            positionY: 0,
        }
    },
    methods: {
        /**
         * Закрыть контекстное меню и сбросить выбранный элемент
         */
        closeAndResetContextMenu() {
            this.isVisible = false;
            this.item = undefined;
        },

        /**
         * Показать контекстное меню
         * @param {MouseEvent} event - Событие клика мыши
         */
        showContextMenu(event) {
            // Проверка роли пользователя
            if (!this.isAdmin) {
                return; // Если пользователь не админ, не отображаем контекстное меню
            }

            // Показать контекстное меню
            this.isVisible = true;
            this.positionX = event.clientX;
            this.positionY = event.clientY;

            // Корректировка позиции меню, чтобы оно не выходило за пределы окна
            this.$nextTick(() => {
                const menu = this.$refs.contextmenu;
                if (!menu) {
                    console.error('Элемент contextmenu не найден.');
                    return;
                }

                const hiddenAreaX = window.innerWidth - event.clientX - menu.clientWidth - 25;
                const hiddenAreaY = window.innerHeight - event.clientY - menu.clientHeight - 25;

                this.positionX = hiddenAreaX < 0 ? event.clientX + hiddenAreaX : event.clientX;
                this.positionY = hiddenAreaY < 0 ? event.clientY + hiddenAreaY : event.clientY;
            });
        },
    },
    created() {
        // Подписка на события для управления контекстным меню
        events.$on('context-menu:hide', this.closeAndResetContextMenu);

        events.$on('context-menu:show', (event, item) => {
            // Проверка роли пользователя перед отображением контекстного меню
            if (!this.isAdmin) {
                return; // Если не админ, игнорируем событие
            }

            // Сохранить выбранный элемент
            this.item = item;

            // Показать контекстное меню
            this.showContextMenu(event);
        });

        events.$on('context-menu:current-folder', (folder) => {
            // Проверка роли пользователя перед отображением контекстного меню
            if (!this.isAdmin) {
                return; // Если не админ, игнорируем событие
            }

            this.item = folder;
            this.isVisible = !this.isVisible;

            if (this.isVisible) {
                const container = document.getElementById('folder-actions').getBoundingClientRect();
                this.positionX = container.x;
                this.positionY = container.y + 20;
            }
        });

        // Добавление обработчика события закрытия контекстного меню при клике вне его
        document.addEventListener('click', this.closeAndResetContextMenu);
    },
    beforeDestroy() {
        // Удаление обработчиков событий при уничтожении компонента
        events.$off('context-menu:hide', this.closeAndResetContextMenu);
        events.$off('context-menu:show');
        events.$off('context-menu:current-folder');
        document.removeEventListener('click', this.closeAndResetContextMenu);
    },
}
</script>

<style scoped>
/* Ваши стили */
</style>
