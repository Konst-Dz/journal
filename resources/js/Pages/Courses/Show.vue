<script setup>
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import MultiSelect from 'primevue/multiselect';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    course: Object,
    availableStudents: Array,
});

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

const enrollForm = useForm({
    user_ids: [],
});

const enroll = () => {
    enrollForm.post(`/courses/${props.course.id}/enroll`, {
        onSuccess: () => {
            enrollForm.reset();
        },
    });
};

const unenroll = (userId) => {
    router.delete(`/courses/${props.course.id}/students/${userId}`, {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout>
        <div class="max-w-7xl mx-auto">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl font-bold">{{ course.name }}</h1>
                    <Tag :value="statusLabels[course.status]" :severity="statusSeverity[course.status]" class="mt-2" />
                </div>
                <div class="flex gap-2">
                    <Button label="Редактировать" icon="pi pi-pencil" @click="router.visit(`/courses/${course.id}/edit`)" />
                    <Button label="Занятия" icon="pi pi-calendar" @click="router.visit(`/courses/${course.id}/lessons`)" />
                    <Button label="Посещаемость" icon="pi pi-chart-bar" @click="router.visit(`/courses/${course.id}/attendance/report`)" />
                    <Button label="Оценки" icon="pi pi-star" @click="router.visit(`/courses/${course.id}/grades`)" />
                    <Button label="Аналитика" icon="pi pi-chart-line" @click="router.visit(`/courses/${course.id}/analytics`)" />
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <Card>
                    <template #title>Информация</template>
                    <template #content>
                        <div class="flex flex-col gap-2">
                            <div><strong>Преподаватель:</strong> {{ course.teacher.name }}</div>
                            <div><strong>Начало:</strong> {{ new Date(course.start_date).toLocaleDateString('ru-RU') }}</div>
                            <div v-if="course.end_date">
                                <strong>Окончание:</strong> {{ new Date(course.end_date).toLocaleDateString('ru-RU') }}
                            </div>
                            <div v-if="course.description" class="mt-2">
                                <strong>Описание:</strong>
                                <p class="mt-1">{{ course.description }}</p>
                            </div>
                        </div>
                    </template>
                </Card>

                <Card>
                    <template #title>Статистика</template>
                    <template #content>
                        <div class="flex flex-col gap-2">
                            <div><strong>Студентов:</strong> {{ course.students?.length || 0 }}</div>
                            <div><strong>Занятий:</strong> {{ course.lessons?.length || 0 }}</div>
                        </div>
                    </template>
                </Card>
            </div>

            <Card>
                <template #title>Студенты</template>
                <template #content>
                    <div class="mb-4">
                        <form @submit.prevent="enroll" class="flex gap-2">
                            <MultiSelect
                                v-model="enrollForm.user_ids"
                                :options="availableStudents"
                                optionLabel="name"
                                optionValue="id"
                                placeholder="Выберите сотрудников"
                                class="flex-1"
                            />
                            <Button type="submit" label="Добавить" :loading="enrollForm.processing" />
                        </form>
                    </div>

                    <DataTable :value="course.students" stripedRows>
                        <Column field="name" header="Имя" />
                        <Column field="email" header="Email" />
                        <Column header="Действия" style="width: 100px">
                            <template #body="{ data }">
                                <Button icon="pi pi-times" severity="danger" text rounded @click="unenroll(data.id)" />
                            </template>
                        </Column>
                    </DataTable>
                </template>
            </Card>
        </div>
    </AppLayout>
</template>


