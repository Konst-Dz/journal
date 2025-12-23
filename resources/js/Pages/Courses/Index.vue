<script setup>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import Tag from 'primevue/tag';
import ConfirmDialog from 'primevue/confirmdialog';
import { useConfirm } from 'primevue/useconfirm';

const props = defineProps({
    courses: Object,
    filters: Object,
});

const confirm = useConfirm();
const search = ref(props.filters.search || '');
const status = ref(props.filters.status || null);

const statuses = [
    { label: 'Все статусы', value: null },
    { label: 'Черновик', value: 'draft' },
    { label: 'Активный', value: 'active' },
    { label: 'Завершён', value: 'completed' },
];

const statusSeverity = {
    draft: 'secondary',
    active: 'success',
    completed: 'info',
};

const statusLabels = {
    draft: 'Черновик',
    active: 'Активный',
    completed: 'Завершён',
};

let timeout;
watch([search, status], () => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get('/courses', { search: search.value, status: status.value }, { preserveState: true });
    }, 300);
});

const deleteCourse = (course) => {
    confirm.require({
        message: `Удалить курс "${course.name}"?`,
        header: 'Подтверждение',
        icon: 'pi pi-exclamation-triangle',
        acceptLabel: 'Удалить',
        rejectLabel: 'Отмена',
        accept: () => router.delete(`/courses/${course.id}`),
    });
};
</script>

<template>
    <AppLayout>
        <ConfirmDialog />
        <div class="max-w-7xl mx-auto">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Курсы</h1>
                <Button label="Создать курс" icon="pi pi-plus" @click="router.visit('/courses/create')" />
            </div>

            <div class="flex gap-4 mb-4">
                <InputText v-model="search" placeholder="Поиск..." class="w-64" />
                <Select v-model="status" :options="statuses" optionLabel="label" optionValue="value" placeholder="Статус" class="w-48" />
            </div>

            <DataTable :value="courses.data" stripedRows>
                <Column field="name" header="Название" @click="router.visit(`/courses/${data.id}`)" />
                <Column field="teacher.name" header="Преподаватель" />
                <Column field="status" header="Статус">
                    <template #body="{ data }">
                        <Tag :value="statusLabels[data.status]" :severity="statusSeverity[data.status]" />
                    </template>
                </Column>
                <Column field="start_date" header="Начало">
                    <template #body="{ data }">
                        {{ new Date(data.start_date).toLocaleDateString('ru-RU') }}
                    </template>
                </Column>
                <Column header="Действия" style="width: 200px">
                    <template #body="{ data }">
                        <div class="flex gap-2">
                            <Button icon="pi pi-eye" severity="info" text rounded @click="router.visit(`/courses/${data.id}`)" />
                            <Button icon="pi pi-pencil" severity="warning" text rounded @click="router.visit(`/courses/${data.id}/edit`)" />
                            <Button icon="pi pi-trash" severity="danger" text rounded @click="deleteCourse(data)" />
                        </div>
                    </template>
                </Column>
            </DataTable>
        </div>
    </AppLayout>
</template>


