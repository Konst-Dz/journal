<script setup>
import { useForm } from '@inertiajs/vue3';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import Button from 'primevue/button';
import Message from 'primevue/message';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post('/register');
};
</script>

<template>
    <div class="min-h-screen flex items-center justify-center bg-surface-50">
        <div class="w-full max-w-md p-8 bg-white rounded-xl shadow-lg">
            <h1 class="text-2xl font-bold text-center mb-6">Регистрация</h1>

            <form @submit.prevent="submit" class="flex flex-col gap-4">
                <div class="flex flex-col gap-2">
                    <label for="name">Имя</label>
                    <InputText
                        id="name"
                        v-model="form.name"
                        placeholder="Иван Иванов"
                        :invalid="!!form.errors.name"
                        class="w-full"
                    />
                    <Message v-if="form.errors.name" severity="error" size="small">
                        {{ form.errors.name }}
                    </Message>
                </div>

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
                    <Message v-if="form.errors.email" severity="error" size="small">
                        {{ form.errors.email }}
                    </Message>
                </div>

                <div class="flex flex-col gap-2">
                    <label for="password">Пароль</label>
                    <Password
                        id="password"
                        v-model="form.password"
                        toggleMask
                        :invalid="!!form.errors.password"
                        class="w-full"
                        inputClass="w-full"
                    />
                    <Message v-if="form.errors.password" severity="error" size="small">
                        {{ form.errors.password }}
                    </Message>
                </div>

                <div class="flex flex-col gap-2">
                    <label for="password_confirmation">Подтверждение пароля</label>
                    <Password
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        :feedback="false"
                        toggleMask
                        class="w-full"
                        inputClass="w-full"
                    />
                </div>

                <Button
                    type="submit"
                    label="Зарегистрироваться"
                    :loading="form.processing"
                    class="w-full"
                />

                <p class="text-center text-sm text-surface-500">
                    Уже есть аккаунт?
                    <a href="/login" class="text-primary-500 hover:underline">Войти</a>
                </p>
            </form>
        </div>
    </div>
</template>


