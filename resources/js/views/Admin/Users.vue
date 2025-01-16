<!-- Users.vue -->
<template>
    <div>
        <!-- Authorized Content -->
        <div v-if="isAdminOrHelper">
            <div class="card shadow-card">
                <div class="mb-6 flex flex-wrap gap-2">
                    <router-link :to="{ name: 'UserCreate' }">
                        <MobileActionButton icon="user-plus">
                            {{ $t('admin_page_user.create_user.submit') }}
                        </MobileActionButton>
                    </router-link>

                    <MobileActionButton @click.native="$openSpotlight('users')" icon="search">
                        {{ $t('search') }}
                    </MobileActionButton>
                </div>

                <!-- Datatable -->
                <DatatableWrapper
                    @init="isLoading = false"
                    api="/api/admin/users"
                    :paginator="true"
                    :columns="columns"
                    class="overflow-x-auto"
                >
                    <template #default="{ row }">
                        <!-- User Row -->
                        <tr
                            class="whitespace-nowrap border-b border-dashed border-light dark:border-opacity-5"
                        >
                            <!-- User Details -->
                            <td class="py-3 pr-3 md:pr-1">
                                <router-link
                                    :to="{ name: 'UserDetail', params: { id: row.data.id } }"
                                >
                                    <div class="flex items-center">
                                        <MemberAvatar
                                            :is-border="false"
                                            :size="44"
                                            :member="row"
                                        />
                                        <div class="ml-3 pr-10">
                                            <b
                                                class="block overflow-hidden text-ellipsis whitespace-nowrap text-sm font-bold"
                                                style="max-width: 155px"
                                            >
                                                {{ row.data.relationships.settings.data.attributes.name }}
                                            </b>
                                            <span class="block text-xs text-gray-600 dark:text-gray-500">
                                                {{ row.data.attributes.email }}
                                            </span>
                                        </div>
                                    </div>
                                </router-link>
                            </td>

                            <!-- Role -->
                            <td class="px-3 md:px-1">
                                <ColorLabel :color="$getUserRoleColor(row.data.attributes.role)">
                                    {{ $t(row.data.attributes.role) }}
                                </ColorLabel>
                            </td>

                            <!-- Created At -->
                            <td class="px-3 md:px-1">
                                <span class="text-sm font-bold">
                                    {{ row.data.attributes.created_at }}
                                </span>
                            </td>

                            <!-- Actions -->
                            <td class="pl-3 text-right md:pl-1">
                                <div class="flex justify-end space-x-2">
                                    <router-link
                                        class="flex h-8 w-8 items-center justify-center rounded-md bg-light-background transition-colors hover:bg-green-100 dark:bg-2x-dark-foreground"
                                        :to="{ name: 'UserDetail', params: { id: row.data.id } }"
                                    >
                                        <Edit2Icon size="15" class="opacity-75" />
                                    </router-link>
                                    <router-link
                                        class="flex h-8 w-8 items-center justify-center rounded-md bg-light-background transition-colors hover:bg-red-100 dark:bg-2x-dark-foreground"
                                        :to="{ name: 'UserDelete', params: { id: row.data.id } }"
                                    >
                                        <Trash2Icon size="15" class="opacity-75" />
                                    </router-link>
                                </div>
                            </td>
                        </tr>
                    </template>
                </DatatableWrapper>
            </div>
            <div id="loader" v-if="isLoading" class="flex justify-center items-center">
                <Spinner />
            </div>
        </div>

        <!-- Unauthorized Message -->
        <div v-else class="text-center mt-10">
            <h2 class="text-2xl font-bold text-red-600">
                {{ $t('not_authorized') }}
            </h2>
            <p class="mt-4 text-gray-600">
                {{ $t('you_do_not_have_permission_to_view_this_page') }}
            </p>
        </div>
    </div>
</template>

<script>
import MemberAvatar from '../../components/UI/Others/MemberAvatar';
import DatatableWrapper from '../../components/UI/Table/DatatableWrapper';
import MobileActionButton from '../../components/UI/Buttons/MobileActionButton';
import ColorLabel from '../../components/UI/Labels/ColorLabel';
import Spinner from '../../components/UI/Others/Spinner';
import { Trash2Icon, Edit2Icon } from 'vue-feather-icons';
import { mapGetters } from 'vuex';

export default {
    name: 'Users',
    components: {
        DatatableWrapper,
        MemberAvatar,
        MobileActionButton,
        ColorLabel,
        Spinner,
        Trash2Icon,
        Edit2Icon,
    },
    computed: {
        ...mapGetters(['config', 'user']),

        /**
         * Проверка, является ли пользователь администратором или помощником
         */
        isAdminOrHelper() {
            return this.user && ['admin', 'helper'].includes(this.user.data.attributes.role);
        },

        /**
         * Определение колонок для таблицы
         */
        columns() {
            return [
                {
                    label: this.$t('user'),
                    field: 'email',
                    sortable: true,
                },
                {
                    label: this.$t('role'),
                    field: 'role',
                    sortable: true,
                },
                {
                    label: this.$t('created_at'),
                    field: 'created_at',
                    sortable: true,
                },
                {
                    label: this.$t('action'),
                    sortable: false,
                },
            ];
        },
    },
    data() {
        return {
            isLoading: true,
        };
    },
    mounted() {
        // If user is not admin or helper, optionally redirect or handle accordingly
        if (!this.isAdminOrHelper) {
            // Option 1: Redirect to a "Not Authorized" page
            // this.$router.push({ name: 'NotAuthorized' });

            // Option 2: Simply stop further execution
            // return;

            // Option 3: Show a notification (requires a notification system)
            // this.$store.dispatch('showNotification', {
            //     type: 'error',
            //     message: this.$t('not_authorized'),
            // });
        }

        // Fetch application data
        this.$store.dispatch('getAppData');
    },
};
</script>

<style scoped lang="scss">
@import '../../../sass/vuefilemanager/variables';

.card {
    /* Your card styles */
}

.shadow-card {
    /* Your shadow styles */
}

.text-center {
    /* Your text-center styles */
}

.text-red-600 {
    color: #e3342f;
}

.text-gray-600 {
    color: #4a5568;
}

.mt-10 {
    margin-top: 2.5rem;
}

/* Add additional styles as needed */
</style>
