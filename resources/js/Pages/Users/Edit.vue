<script setup>
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import Select from 'primevue/select';
import Button from 'primevue/button';
import Message from 'primevue/message';

const props = defineProps({
    user: Object,
});

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    password: '',
    role: props.user.role,
});

const roles = [
    { label: 'Админ', value: 'admin' },
    { label: 'Преподаватель', value: 'teacher' },
    { label: 'Сотрудник', value: 'employee' },
];

const submit = () => {
    form.put(`/users/${props.user.id}`);
};
</script>

<template>
    <AppLayout>
        <div class="max-w-xl mx-auto">
            <h1 class="text-2xl font-bold mb-6">Редактирование пользователя</h1>

            <form @submit.prevent="submit" class="flex flex-col gap-4">
                <div class="flex flex-col gap-2">
                    <label for="name">Имя</label>
                    <InputText id="name" v-model="form.name" :invalid="!!form.errors.name" class="w-full" />
                    <Message v-if="form.errors.name" severity="error" size="small">{{ form.errors.name }}</Message>
                </div>

                <div class="flex flex-col gap-2">
                    <label for="email">Email</label>
                    <InputText id="email" v-model="form.email" type="email" :invalid="!!form.errors.email" class="w-full" />
                    <Message v-if="form.errors.email" severity="error" size="small">{{ form.errors.email }}</Message>
                </div>

                <div class="flex flex-col gap-2">
                    <label for="password">Новый пароль (оставьте пустым, чтобы не менять)</label>
                    <Password id="password" v-model="form.password" toggleMask :invalid="!!form.errors.password" class="w-full" inputClass="w-full" />
                    <Message v-if="form.errors.password" severity="error" size="small">{{ form.errors.password }}</Message>
                </div>

                <div class="flex flex-col gap-2">
                    <label for="role">Роль</label>
                    <Select id="role" v-model="form.role" :options="roles" optionLabel="label" optionValue="value" class="w-full" />
                </div>

                <div class="flex gap-2 mt-4">
                    <Button type="submit" label="Сохранить" :loading="form.processing" />
                    <Button type="button" label="Отмена" severity="secondary" @click="$inertia.visit('/users')" />
                </div>
            </form>
        </div>
    </AppLayout>
</template>


