<script setup>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Select from 'primevue/select';
import Tag from 'primevue/tag';
import ConfirmDialog from 'primevue/confirmdialog';
import { useConfirm } from 'primevue/useconfirm';

const props = defineProps({
    course: Object,
    lessons: Object,
    filters: Object,
});

const confirm = useConfirm();
const type = ref(props.filters.type || null);

const types = [
    { label: 'Все типы', value: null },
    { label: 'Лекция', value: 'lecture' },
    { label: 'Практика', value: 'practice' },
    { label: 'Коучинг', value: 'coaching' },
];

const typeSeverity = {
    lecture: 'info',
    practice: 'success',
    coaching: 'warning',
};

const typeLabels = {
    lecture: 'Лекция22',
    practice: 'Практика',
    coaching: 'Коучинг',
};

const statusLabels = {
    pending: 'В ожидании',
    completed: 'Закончен',
    cancelled: 'Отменен',
};

const statusSeverity = {
    pending: 'warning',
    completed: 'success',
    cancelled: 'danger',
};

const statusOptions = [
    { label: 'В ожидании', value: 'pending' },
    { label: 'Закончен', value: 'completed' },
    { label: 'Отменен', value: 'cancelled' },
];

const updateStatus = (lesson, newStatus) => {
    router.put(`/courses/${props.course.id}/lessons/${lesson.id}`, {
        title: lesson.title,
        description: lesson.description,
        scheduled_at: lesson.scheduled_at,
        duration_minutes: lesson.duration_minutes,
        type: lesson.type,
        status: newStatus,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

watch(type, () => {
    router.get(`/courses/${props.course.id}/lessons`, { type: type.value }, { preserveState: true });
});

const deleteLesson = (lesson) => {
    confirm.require({
        message: `Удалить занятие "${lesson.title}"?`,
        header: 'Подтверждение',
        icon: 'pi pi-exclamation-triangle',
        acceptLabel: 'Удалить',
        rejectLabel: 'Отмена',
        accept: () => router.delete(`/courses/${props.course.id}/lessons/${lesson.id}`),
    });
};
</script>

<template>
    <AppLayout>
        <ConfirmDialog />
        <div class="max-w-7xl mx-auto">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl font-bold">Занятия: {{ course.name }}</h1>
                </div>
                <Button label="Создать занятие" icon="pi pi-plus" @click="router.visit(`/courses/${course.id}/lessons/create`)" />
            </div>

            <div class="flex gap-4 mb-4">
                <Select v-model="type" :options="types" optionLabel="label" optionValue="value" placeholder="Тип" class="w-48" />
            </div>

            <DataTable :value="lessons.data" stripedRows>
                <Column field="title" header="Название" />
                <Column field="type" header="Тип">
                    <template #body="{ data }">
                        <Tag :value="typeLabels[data.type]" :severity="typeSeverity[data.type]" />
                    </template>
                </Column>
                <Column field="scheduled_at" header="Дата и время">
                    <template #body="{ data }">
                        {{ new Date(data.scheduled_at).toLocaleString('ru-RU') }}
                    </template>
                </Column>
                <Column field="duration_minutes" header="Длительность">
                    <template #body="{ data }">
                        {{ data.duration_minutes }} мин
                    </template>
                </Column>
                <Column field="status" header="Статус">
                    <template #body="{ data }">
                        <Select 
                            :modelValue="data.status" 
                            :options="statusOptions" 
                            optionLabel="label" 
                            optionValue="value"
                            @update:modelValue="(value) => updateStatus(data, value)"
                            class="w-40"
                        />
                    </template>
                </Column>
                <Column header="Действия" style="width: 200px">
                    <template #body="{ data }">
                        <div class="flex gap-2">
                            <Button icon="pi pi-check-circle" severity="info" text rounded @click="router.visit(`/courses/${course.id}/lessons/${data.id}/attendance`)" v-tooltip.bottom="'Посещаемость'" />
                            <Button icon="pi pi-pencil" severity="warning" text rounded @click="router.visit(`/courses/${course.id}/lessons/${data.id}/edit`)" />
                            <Button icon="pi pi-trash" severity="danger" text rounded @click="deleteLesson(data)" />
                        </div>
                    </template>
                </Column>
            </DataTable>
        </div>
    </AppLayout>
</template>


