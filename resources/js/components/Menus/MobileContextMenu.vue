<!-- MobileContextMenu.vue -->
<template>
    <!-- Отображать MenuMobile только если пользователь является администратором -->
    <MenuMobile v-if="isAdmin" name="file-menu">
        <ThumbnailItem class="m-5" :item="clipboard[0]" />

        <MenuMobileGroup v-if="$slots.default">
            <slot />
        </MenuMobileGroup>

        <MenuMobileGroup v-if="$slots.editor && $checkPermission('editor')">
            <slot name="editor" />
        </MenuMobileGroup>

        <MenuMobileGroup v-if="$slots.visitor && $checkPermission('visitor')">
            <slot name="visitor" />
        </MenuMobileGroup>
    </MenuMobile>
</template>

<script>
import MenuMobileGroup from '../Mobile/MenuMobileGroup'
import ThumbnailItem from '../UI/Entries/ThumbnailItem'
import MenuMobile from '../Mobile/MenuMobile'
import { mapGetters } from 'vuex'

export default {
    name: 'MobileContextMenu',
    components: {
        MenuMobileGroup,
        ThumbnailItem,
        MenuMobile,
    },
    computed: {
        ...mapGetters(['clipboard', 'user']),

        /**
         * Проверка, является ли пользователь администратором
         */
        isAdmin() {
            return this.user && this.user.data.attributes.role === 'admin';
        },
    },
}
</script>
