<template>
    <div
        v-show="isVisible"
        :style="{ top: positionY + 'px', left: positionX + 'px' }"
        @click="closeAndResetContextMenu"
        class="fixed z-20 w-60 select-none overflow-hidden rounded-xl bg-white shadow-lg dark:bg-2x-dark-foreground"
        ref="contextmenu"
    >
        <div class="w-full">
            <!--Show empty select contextmenu-->
            <slot name="empty-select" v-if="!item" />

            <!--Show single select contextmenu-->
            <slot name="single-select" v-if="isMultiSelectContextMenu" />

            <!--Show multiple select contextmenu-->
            <slot name="multiple-select" v-if="!isMultiSelectContextMenu" />
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

        // Новое вычисляемое свойство для проверки роли
        isAdmin() {
            return this.user && this.user.data.attributes.role === 'admin';
        },

        isMultiSelectContextMenu() {
            // Если контекстное меню открыто для нескольких выбранных элементов, отображаем соответствующие опции
            if (this.clipboard.length > 1 && this.clipboard.includes(this.item)) return false

            // Если контекстное меню открыто для одного элемента, отображаем соответствующие опции
            if (this.clipboard.length < 2 || !this.clipboard.includes(this.item)) return true
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
        closeAndResetContextMenu() {
            // Закрыть контекстное меню
            this.isVisible = false

            // Сбросить выбранный элемент
            this.item = undefined
        },
        showContextMenu(event) {
            // Проверка роли пользователя
            if (!this.isAdmin) {
                return; // Если пользователь не админ, не отображаем контекстное меню
            }

            // Показать контекстное меню
            this.isVisible = true

            // Установить начальную позицию меню
            this.positionX = event.clientX
            this.positionY = event.clientY

            // Обновление DOM и корректировка позиции меню, если оно выходит за пределы окна
            this.$nextTick(() => {
                const menu = this.$refs.contextmenu
                if (!menu) {
                    console.error('Элемент contextmenu не найден.')
                    return
                }

                const hiddenAreaX = window.innerWidth - event.clientX - menu.clientWidth - 25
                const hiddenAreaY = window.innerHeight - event.clientY - menu.clientHeight - 25

                this.positionX = hiddenAreaX < 0 ? event.clientX + hiddenAreaX : event.clientX
                this.positionY = hiddenAreaY < 0 ? event.clientY + hiddenAreaY : event.clientY
            })
        },
    },
    created() {
        events.$on('context-menu:hide', () => this.closeAndResetContextMenu())

        events.$on('context-menu:show', (event, item) => {
            // Проверка роли пользователя перед отображением контекстного меню
            if (!this.isAdmin) {
                return; // Если не админ, игнорируем событие
            }

            // Сохранить выбранный элемент
            this.item = item

            // Показать контекстное меню
            this.showContextMenu(event)
        })

        events.$on('context-menu:current-folder', (folder) => {
            // Проверка роли пользователя перед отображением контекстного меню
            if (!this.isAdmin) {
                return; // Если не админ, игнорируем событие
            }

            this.item = folder

            this.isVisible = !this.isVisible

            if (this.isVisible) {
                let container = document.getElementById('folder-actions').getBoundingClientRect()

                this.positionX = container.x
                this.positionY = container.y + 20
            }
        })
    },
}
</script>
