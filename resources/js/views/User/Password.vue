<template>
    <div v-if="user">
        <!--Change password-->
        <ValidationObserver
            ref="password"
            @submit.prevent="resetPassword"
            v-slot="{ invalid }"
            tag="form"
            class="card shadow-card"
        >
            <FormLabel>
                {{ $t('user_password.title') }}
            </FormLabel>

            <ValidationProvider tag="div" mode="passive" name="Current Password" rules="required" v-slot="{ errors }">
                <AppInputText :title="$t('current_password')" :error="errors[0]">
                    <input
                        v-model="passwordForm.current"
                        :placeholder="$t('current_password')"
                        type="password"
                        class="focus-border-theme input-dark"
                        :class="{ '!border-rose-600': errors[0] }"
                    />
                </AppInputText>
            </ValidationProvider>

            <ValidationProvider tag="div" mode="passive" name="New Password" rules="required" v-slot="{ errors }">
                <AppInputText :title="$t('new_password')" :error="errors[0]">
                    <input
                        v-model="passwordForm.password"
                        :placeholder="$t('new_password')"
                        type="password"
                        class="focus-border-theme input-dark"
                        :class="{ '!border-rose-600': errors[0] }"
                    />
                </AppInputText>
            </ValidationProvider>

            <ValidationProvider
                tag="div"
                mode="passive"
                name="Confirm Your Password"
                rules="required"
                v-slot="{ errors }"
            >
                <AppInputText :title="$t('confirm_password')" :error="errors[0]">
                    <input
                        v-model="passwordForm.password_confirmation"
                        :placeholder="$t('confirm_password')"
                        type="password"
                        class="focus-border-theme input-dark"
                        :class="{ '!border-rose-600': errors[0] }"
                    />
                </AppInputText>
            </ValidationProvider>

            <ButtonBase type="submit" button-style="theme" class="w-full sm:w-auto">
                {{ $t('profile.store_pass') }}
            </ButtonBase>
        </ValidationObserver>
    </div>
</template>

<script>
import { ValidationProvider, ValidationObserver } from 'vee-validate/dist/vee-validate.full'
import FormLabel from '../../components/UI/Labels/FormLabel'
import ButtonBase from '../../components/UI/Buttons/ButtonBase'
import AppInputText from '../../components/Forms/Layouts/AppInputText'
import { events } from '../../bus'
import { mapGetters } from 'vuex'
import axios from 'axios'

export default {
    name: 'Password',
    components: {
        ValidationProvider,
        ValidationObserver,
        AppInputText,
        ButtonBase,
        FormLabel,
    },
    computed: {
        ...mapGetters(['user']),
    },
    data() {
        return {
            passwordForm: {
                current: '',
                password: '',
                password_confirmation: '',
            },
            isLoading: false,
        }
    },
    methods: {
        async resetPassword() {
            // Валидация полей
            const isValid = await this.$refs.password.validate()

            if (!isValid) return

            // Отправка запроса на обновление пароля
            axios
                .post(`${this.$store.getters.api}/user/password`, this.passwordForm)
                .then(() => {
                    // Сброс полей ввода
                    this.passwordForm = {
                        current: '',
                        password: '',
                        password_confirmation: '',
                    }

                    // Сброс ошибок валидации
                    this.$refs.password.reset()

                    // Показать сообщение об успешном изменении пароля
                    events.$emit('success:open', {
                        title: this.$t('popup_pass_changed.title'),
                        message: this.$t('popup_pass_changed.message'),
                    })
                })
                .catch((error) => {
                    if (error.response && error.response.status === 422) {
                        if (error.response.data.errors['password']) {
                            this.$refs.password.setErrors({
                                'New Password': error.response.data.errors['password'],
                            })
                        }

                        if (error.response.data.errors['current']) {
                            this.$refs.password.setErrors({
                                'Current Password': error.response.data.errors['current'],
                            })
                        }
                    } else {
                        // Обработка других ошибок, если необходимо
                        this.$isSomethingWrong()
                    }
                })
        },
    },
    created() {
        // Удалены методы и обработчики, связанные с 2FA и Personal Access Token
    },
    destroyed() {
        // Удалены обработчики событий, связанные с 2FA и Personal Access Token
    },
}
</script>
