<script setup>
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Calendar from 'primevue/calendar';
import Select from 'primevue/select';
import Button from 'primevue/button';
import Message from 'primevue/message';

const form = useForm({
    name: '',
    description: '',
    status: 'draft',
    start_date: null,
    end_date: null,
});

const statuses = [
    { label: 'Черновик', value: 'draft' },
    { label: 'Активный', value: 'active' },
    { label: 'Завершён', value: 'completed' },
];

const submit = () => {
    form.post('/courses');
};
</script>

<template>
    <AppLayout>
        <div class="max-w-xl mx-auto">
            <h1 class="text-2xl font-bold mb-6">Новый курс</h1>

            <form @submit.prevent="submit" class="flex flex-col gap-4">
                <div class="flex flex-col gap-2">
                    <label for="name">Название *</label>
                    <InputText id="name" v-model="form.name" :invalid="!!form.errors.name" class="w-full" />
                    <Message v-if="form.errors.name" severity="error" size="small">{{ form.errors.name }}</Message>
                </div>

                <div class="flex flex-col gap-2">
                    <label for="description">Описание</label>
                    <Textarea id="description" v-model="form.description" rows="4" :invalid="!!form.errors.description" class="w-full" />
                    <Message v-if="form.errors.description" severity="error" size="small">{{ form.errors.description }}</Message>
                </div>

                <div class="flex flex-col gap-2">
                    <label for="status">Статус *</label>
                    <Select id="status" v-model="form.status" :options="statuses" optionLabel="label" optionValue="value" class="w-full" />
                </div>

                <div class="flex flex-col gap-2">
                    <label for="start_date">Дата начала *</label>
                    <Calendar id="start_date" v-model="form.start_date" dateFormat="yy-mm-dd" :invalid="!!form.errors.start_date" class="w-full" />
                    <Message v-if="form.errors.start_date" severity="error" size="small">{{ form.errors.start_date }}</Message>
                </div>

                <div class="flex flex-col gap-2">
                    <label for="end_date">Дата окончания</label>
                    <Calendar id="end_date" v-model="form.end_date" dateFormat="yy-mm-dd" :invalid="!!form.errors.end_date" class="w-full" />
                    <Message v-if="form.errors.end_date" severity="error" size="small">{{ form.errors.end_date }}</Message>
                </div>

                <div class="flex gap-2 mt-4">
                    <Button type="submit" label="Создать" :loading="form.processing" />
                    <Button type="button" label="Отмена" severity="secondary" @click="$inertia.visit('/courses')" />
                </div>
            </form>
        </div>
    </AppLayout>
</template>


