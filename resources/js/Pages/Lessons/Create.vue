<script setup>
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Calendar from 'primevue/calendar';
import InputNumber from 'primevue/inputnumber';
import Select from 'primevue/select';
import Button from 'primevue/button';
import Message from 'primevue/message';

const props = defineProps({
    course: Object,
});

const form = useForm({
    title: '',
    description: '',
    scheduled_at: null,
    duration_minutes: 60,
    type: 'lecture',
    status: 'pending',
});

const types = [
    { label: 'Лекция', value: 'lecture' },
    { label: 'Практика', value: 'practice' },
    { label: 'Коучинг', value: 'coaching' },
];

const statuses = [
    { label: 'В ожидании', value: 'pending' },
    { label: 'Закончен', value: 'completed' },
    { label: 'Отменен', value: 'cancelled' },
];

const submit = () => {
    form.post(`/courses/${props.course.id}/lessons`);
};
</script>

<template>
    <AppLayout>
        <div class="max-w-xl mx-auto">
            <h1 class="text-2xl font-bold mb-6">Новое занятие: {{ course.name }}</h1>

            <form @submit.prevent="submit" class="flex flex-col gap-4">
                <div class="flex flex-col gap-2">
                    <label for="title">Название *</label>
                    <InputText id="title" v-model="form.title" :invalid="!!form.errors.title" class="w-full" />
                    <Message v-if="form.errors.title" severity="error" size="small">{{ form.errors.title }}</Message>
                </div>

                <div class="flex flex-col gap-2">
                    <label for="description">Описание</label>
                    <Textarea id="description" v-model="form.description" rows="4" :invalid="!!form.errors.description" class="w-full" />
                    <Message v-if="form.errors.description" severity="error" size="small">{{ form.errors.description }}</Message>
                </div>

                <div class="flex flex-col gap-2">
                    <label for="scheduled_at">Дата и время *</label>
                    <Calendar id="scheduled_at" v-model="form.scheduled_at" showTime hourFormat="24" :invalid="!!form.errors.scheduled_at" class="w-full" />
                    <Message v-if="form.errors.scheduled_at" severity="error" size="small">{{ form.errors.scheduled_at }}</Message>
                </div>

                <div class="flex flex-col gap-2">
                    <label for="duration_minutes">Длительность (минуты) *</label>
                    <InputNumber id="duration_minutes" v-model="form.duration_minutes" :min="1" :invalid="!!form.errors.duration_minutes" class="w-full" />
                    <Message v-if="form.errors.duration_minutes" severity="error" size="small">{{ form.errors.duration_minutes }}</Message>
                </div>

                <div class="flex flex-col gap-2">
                    <label for="type">Тип *</label>
                    <Select id="type" v-model="form.type" :options="types" optionLabel="label" optionValue="value" class="w-full" />
                </div>

                <div class="flex flex-col gap-2">
                    <label for="status">Статус *</label>
                    <Select id="status" v-model="form.status" :options="statuses" optionLabel="label" optionValue="value" class="w-full" />
                </div>

                <div class="flex gap-2 mt-4">
                    <Button type="submit" label="Создать" :loading="form.processing" />
                    <Button type="button" label="Отмена" severity="secondary" @click="$inertia.visit(`/courses/${course.id}/lessons`)" />
                </div>
            </form>
        </div>
    </AppLayout>
</template>


