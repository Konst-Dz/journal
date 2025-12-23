<script setup>
import { useForm } from '@inertiajs/vue3';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import Button from 'primevue/button';
import Checkbox from 'primevue/checkbox';
import Message from 'primevue/message';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post('/login');
};
</script>

<template>
    <div class="min-h-screen flex items-center justify-center bg-surface-50">
        <div class="w-full max-w-md p-8 bg-white rounded-xl shadow-lg">
            <h1 class="text-2xl font-bold text-center mb-6">Вход в систему</h1>

            <Message v-if="form.errors.email" severity="error" class="mb-4">
                {{ form.errors.email }}
            </Message>

            <form @submit.prevent="submit" class="flex flex-col gap-4">
                <div class="flex flex-col gap-2">
                    <label for="email">Email</label>
                    <InputText
                        id="email"
                        v-model="form.email"
                        type="email"
                        placeholder="email@example.com"
                        :invalid="!!form.errors.email"
                        class="w-full"
                    />
                </div>

                <div class="flex flex-col gap-2">
                    <label for="password">Пароль</label>
                    <Password
                        id="password"
                        v-model="form.password"
                        :feedback="false"
                        toggleMask
                        :invalid="!!form.errors.password"
                        class="w-full"
                        inputClass="w-full"
                    />
                </div>

                <div class="flex items-center gap-2">
                    <Checkbox v-model="form.remember" inputId="remember" binary />
                    <label for="remember">Запомнить меня</label>
                </div>

                <Button
                    type="submit"
                    label="Войти"
                    :loading="form.processing"
                    class="w-full"
                />

                <p class="text-center text-sm text-surface-500">
                    Нет аккаунта?
                    <a href="/register" class="text-primary-500 hover:underline">Регистрация</a>
                </p>
            </form>
        </div>
    </div>
</template>


