<template>
    <div
        class="sticky top-14 z-[19] whitespace-nowrap bg-white dark:bg-dark-background lg:hidden"
    >
        <div class="flex items-center overflow-x-auto pb-3 pl-4">
            <!-- Показываем кнопки, если не в режиме множественного выбора -->
            <slot v-if="!isMultiSelectMode" />

            <!-- Кнопки для режима множественного выбора -->
            <div v-if="isMultiSelectMode">
                <MobileActionButton @click.native="selectAll" icon="check-square">
                    {{ $t('select_all') }}
                </MobileActionButton>
                <MobileActionButton @click.native="deselectAll" icon="x-square">
                    {{ $t('deselect_all') }}
                </MobileActionButton>
                <MobileActionButton @click.native="disableMultiSelectMode" icon="check">
                    {{ $t('done') }}
                </MobileActionButton>
            </div>

            <!-- Кнопка загрузки файла (только для администраторов) -->
            <MobileActionButton icon="cloud-plus" @click.native="handleUpload">
                {{ $t('upload') }}
            </MobileActionButton>
        </div>

        <!-- Прогресс-бар загрузки -->
        <UploadProgress class="pt-3 pl-4" />
    </div>
</template>

<script>
import MobileActionButton from '../UI/Buttons/MobileActionButton'
import UploadProgress from '../UI/Others/UploadProgress'
import { mapGetters } from 'vuex'

export default {
    name: 'FileActionsMobile',
    components: {
        MobileActionButton,
        UploadProgress,
    },
    computed: {
        ...mapGetters(['isMultiSelectMode', 'isAdmin']),
    },
    methods: {
        selectAll() {
            this.$store.commit('ADD_ALL_ITEMS_TO_CLIPBOARD')
        },
        deselectAll() {
            this.$store.commit('CLIPBOARD_CLEAR')
        },
        disableMultiSelectMode() {
            this.$store.commit('TOGGLE_MULTISELECT_MODE')
        },
        handleUpload() {
            // Логика открытия модального окна или активации загрузки файлов
            // Например:
            this.$refs.uploadInput.click()
        },
    },
}
</script>

<style scoped lang="scss">
@import '../../../sass/vuefilemanager/variables';
@import '../../../sass/vuefilemanager/mixins';

.button-enter-active,
.button-leave-active {
    transition: all 250ms;
}

.button-enter {
    opacity: 0;
    transform: translateY(-50%);
}

.button-leave-to {
    opacity: 0;
    transform: translateY(50%);
}

.button-leave-active {
    position: absolute;
}
</style>
