<template>
    <AuthContentWrapper ref="auth" class="h-screen">
        <!--Registration-->
        <AuthContent name="sign-up" :visible="true">
            <Headline :title="$t('page_registration.title')" :description="$t('page_registration.subtitle')" />

            <ValidationObserver
                @submit.prevent="signUp"
                ref="sign_up"
                v-slot="{ invalid }"
                tag="form"
                class="mb-12 space-y-4 text-left"
            >
                <!-- Поля формы регистрации -->

                <div class="mx-auto mb-5 md:flex md:max-w-lg md:items-center">
                    <label class="mb-1.5 block font-bold md:mb-0 md:w-72 md:pr-4 md:text-right">
                        {{ $t('email') }}:
                    </label>
                    <ValidationProvider
                        tag="div"
                        mode="passive"
                        class="w-full text-left"
                        name="E-Mail"
                        rules="required|email"
                        v-slot="{ errors }"
                    >
                        <input
                            v-model="register.email"
                            :placeholder="$t('page_registration.placeholder_email')"
                            type="email"
                            class="dark:placeholder:text-gray-600 focus-border-theme w-full appearance-none rounded-lg border border-transparent bg-light-background px-5 py-3.5 font-bold dark:bg-2x-dark-foreground"
                            :class="{ '!border-rose-600': errors[0] }"
                        />
                        <span class="text-left text-xs text-red-600" v-if="errors[0]">{{ errors[0] }}</span>
                    </ValidationProvider>
                </div>

                <div class="mx-auto mb-5 md:flex md:max-w-lg md:items-center">
                    <label class="mb-1.5 block font-bold md:mb-0 md:w-72 md:pr-4 md:text-right">
                        {{ $t('full_name') }}:
                    </label>
                    <ValidationProvider
                        tag="div"
                        mode="passive"
                        class="w-full text-left"
                        name="Full Name"
                        rules="required"
                        v-slot="{ errors }"
                    >
                        <input
                            v-model="register.name"
                            :placeholder="$t('page_registration.placeholder_name')"
                            type="text"
                            class="dark:placeholder:text-gray-600 focus-border-theme w-full appearance-none rounded-lg border border-transparent bg-light-background px-5 py-3.5 font-bold dark:bg-2x-dark-foreground"
                            :class="{ '!border-rose-600': errors[0] }"
                        />
                        <span class="text-left text-xs text-red-600" v-if="errors[0]">{{ errors[0] }}</span>
                    </ValidationProvider>
                </div>

                <div class="mx-auto mb-5 md:flex md:max-w-lg md:items-center">
                    <label class="mb-1.5 block font-bold md:mb-0 md:w-72 md:pr-4 md:text-right">
                        {{ $t('page_registration.label_pass') }}:
                    </label>
                    <ValidationProvider
                        tag="div"
                        mode="passive"
                        class="w-full text-left"
                        name="Your New Password"
                        rules="required|min:6"
                        v-slot="{ errors }"
                    >
                        <input
                            v-model="register.password"
                            :placeholder="$t('new_password')"
                            type="password"
                            class="dark:placeholder:text-gray-600 focus-border-theme w-full appearance-none rounded-lg border border-transparent bg-light-background px-5 py-3.5 font-bold dark:bg-2x-dark-foreground"
                            :class="{ '!border-rose-600': errors[0] }"
                        />
                        <span class="text-left text-xs text-red-600" v-if="errors[0]">{{ errors[0] }}</span>
                    </ValidationProvider>
                </div>

                <div class="mx-auto mb-5 md:flex md:max-w-lg md:items-center">
                    <label class="mb-1.5 block font-bold md:mb-0 md:w-72 md:pr-4 md:text-right">
                        {{ $t('confirm_password') }}:
                    </label>
                    <ValidationProvider
                        tag="div"
                        mode="passive"
                        class="w-full text-left"
                        name="Confirm Your Password"
                        rules="required|confirmed:password"
                        v-slot="{ errors }"
                    >
                        <input
                            v-model="register.password_confirmation"
                            :placeholder="$t('page_registration.placeholder_confirm_pass')"
                            class="dark:placeholder:text-gray-600 focus-border-theme w-full appearance-none rounded-lg border border-transparent bg-light-background px-5 py-3.5 font-bold dark:bg-2x-dark-foreground"
                            type="password"
                            :class="{ '!border-rose-600': errors[0] }"
                        />
                        <span class="text-left text-xs text-red-600" v-if="errors[0]">{{ errors[0] }}</span>
                    </ValidationProvider>
                </div>

                <div class="text-center">
                    <!-- Удалённый блок с соглашением -->
                    <AuthButton
                        class="w-full justify-center md:w-min"
                        icon="chevron-right"
                        :text="$t('create_account')"
                        :loading="isLoading"
                        :disabled="isLoading"
                    />
                </div>
            </ValidationObserver>

            <SocialLoginButtons />

            <span class="block">
                {{ $t('page_registration.have_an_account') }}
                <router-link :to="{ name: 'SignIn' }" class="text-theme font-bold">
                    {{ $t('log_in') }}
                </router-link>
            </span>
        </AuthContent>
    </AuthContentWrapper>
</template>

<script>
import Headline from '../../components/UI/Labels/LogoHeadline'
import AuthContentWrapper from '../../components/Layout/AuthPages/AuthContentWrapper'
import { ValidationProvider, ValidationObserver } from 'vee-validate/dist/vee-validate.full'
import AuthContent from '../../components/Layout/AuthPages/AuthContent'
import AuthButton from '../../components/UI/Buttons/AuthButton'
import SocialLoginButtons from '../../components/UI/Buttons/SocialLoginButtons'
import { required, email, min, confirmed } from 'vee-validate/dist/rules'
import { extend } from 'vee-validate'
import { mapGetters } from 'vuex'
import { events } from '../../bus'
import axios from 'axios'

export default {
    name: 'SignUp',
    components: {
        SocialLoginButtons,
        AuthContentWrapper,
        ValidationProvider,
        ValidationObserver,
        AuthContent,
        AuthButton,
        Headline,
    },
    computed: {
        ...mapGetters(['config']),
        privacyPolicy() {
            return this.config.legal.find((legal) => {
                return legal.slug === 'privacy-policy'
            })
        },
        termsOfService() {
            return this.config.legal.find((legal) => {
                return legal.slug === 'terms-of-service'
            })
        },
    },
    data() {
        return {
            isLoading: false,
            register: {
                name: '',
                email: '',
                password: '',
                password_confirmation: '',
                reCaptcha: null,
            },
        }
    },
    methods: {
        async signUp() {
            // Validate fields
            const isValid = await this.$refs.sign_up.validate()

            if (!isValid) return

            // Start loading
            this.isLoading = true

            // Get ReCaptcha token
            if (this.config.allowedRecaptcha) {
                this.register.reCaptcha = await this.$reCaptchaToken('register')
                    .then((response) => response)
            }

            // Send request to register user
            axios
                .post('/api/register', this.register)
                .then(() => {
                    if (!this.config.userVerification) {
                        // Set login state
                        this.$store.commit('SET_AUTHORIZED', true)

                        // Navigate to files page
                        this.$router.push({ name: 'Files' })
                    } else {
                        // Navigate to SuccessfullySend page
                        this.$router.push({ name: 'SuccessfullySend' })
                    }
                })
                .catch((error) => {
                    if (error.response) {
                        const { status, data } = error.response

                        if (status === 500) {
                            events.$emit('alert:open', {
                                title: this.$t('popup_error.title'),
                                message: this.$t('popup_error.message'),
                            })
                        }

                        if (status === 409) {
                            events.$emit('alert:open', {
                                title: data.message,
                            })
                        }

                        if (status === 422) {
                            const errors = {}

                            if (data.errors['email']) {
                                errors['E-Mail'] = data.errors['email']
                            }

                            if (data.errors['password']) {
                                errors['Your New Password'] = data.errors['password']
                            }

                            if (data.errors['reCaptcha']) {
                                events.$emit('alert:open', {
                                    title: data.message,
                                })
                            }

                            this.$refs.sign_up.setErrors(errors)
                        }
                    } else {
                        // Handle network or other errors
                        events.$emit('alert:open', {
                            title: this.$t('popup_error.title'),
                            message: this.$t('popup_error.network_error'),
                        })
                    }
                })
                .finally(() => {
                    // End loading
                    this.isLoading = false
                })
        },
    },
    created() {
        this.$scrollTop()

        // Redirect if user is authenticated
        if (this.$root.$data.config.isAuthenticated) {
            this.$router.push({ name: 'Files' })
        }

        if (this.config.isPrefilledUsers) {
            this.register = {
                name: 'John Doe',
                email: 'demo-' + Math.floor(Math.random() * 100000) + '@doe.com',
                password: 'vuefilemanager',
                password_confirmation: 'vuefilemanager',
                reCaptcha: null,
            }
        }
    },
}

// Добавляем валидации
extend('required', {
    ...required,
    message: 'Это поле обязательно.',
})

extend('email', {
    ...email,
    message: 'Введите корректный email.',
})

extend('min', {
    ...min,
    message: 'Пароль должен содержать как минимум {length} символов.',
})

extend('confirmed', {
    ...confirmed,
    message: 'Пароли не совпадают.',
})
</script>


