<script setup>
import { useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Select from 'primevue/select';
import Textarea from 'primevue/textarea';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Breadcrumb from 'primevue/breadcrumb';

const props = defineProps({
    course: Object,
    lesson: Object,
    students: Array,
});

const form = useForm({
    attendances: props.students.map((s) => ({
        user_id: s.id,
        status: s.attendance?.status || 'absent',
        note: s.attendance?.note || '',
    })),
});

const statuses = [
    { label: 'Присутствует', value: 'present' },
    { label: 'Отсутствует', value: 'absent' },
    { label: 'Опоздал', value: 'late' },
    { label: 'Уважительная причина', value: 'excused' },
];

const statusSeverity = {
    present: 'success',
    absent: 'danger',
    late: 'warning',
    excused: 'info',
};

const statusLabels = {
    present: 'Присутствует',
    absent: 'Отсутствует',
    late: 'Опоздал',
    excused: 'Уважительная причина',
};

const breadcrumbItems = [
    { label: 'Курсы', url: '/courses' },
    { label: props.course.name, url: `/courses/${props.course.id}` },
    { label: 'Занятия', url: `/courses/${props.course.id}/lessons` },
    { label: props.lesson.title, url: `/courses/${props.course.id}/lessons/${props.lesson.id}` },
    { label: 'Посещаемость' },
];

const submit = () => {
    form.post(`/courses/${props.course.id}/lessons/${props.lesson.id}/attendance`);
};
</script>

<template>
    <AppLayout>
        <div class="max-w-7xl mx-auto">
            <Breadcrumb :model="breadcrumbItems" class="mb-4">
                <template #item="{ item }">
                    <a v-if="item.url" @click.prevent="router.visit(item.url)" class="text-primary hover:underline cursor-pointer">{{ item.label }}</a>
                    <span v-else>{{ item.label }}</span>
                </template>
            </Breadcrumb>
            
            <div class="mb-6">
                <h1 class="text-2xl font-bold">Посещаемость</h1>
                <p class="text-surface-500">{{ course.name }} - {{ lesson.title }}</p>
                <p class="text-surface-500">{{ new Date(lesson.scheduled_at).toLocaleString('ru-RU') }}</p>
            </div>

            <form @submit.prevent="submit">
                <DataTable :value="students" stripedRows>
                    <Column field="name" header="Студент" />
                    <Column header="Статус">
                        <template #body="{ data }">
                            <Select
                                :model-value="form.attendances.find(a => a.user_id === data.id)?.status"
                                @update:model-value="(val) => {
                                    const att = form.attendances.find(a => a.user_id === data.id);
                                    if (att) att.status = val;
                                }"
                                :options="statuses"
                                optionLabel="label"
                                optionValue="value"
                                class="w-full"
                            />
                        </template>
                    </Column>
                    <Column header="Примечание">
                        <template #body="{ data }">
                            <Textarea
                                :model-value="form.attendances.find(a => a.user_id === data.id)?.note"
                                @update:model-value="(val) => {
                                    const att = form.attendances.find(a => a.user_id === data.id);
                                    if (att) att.note = val;
                                }"
                                rows="2"
                                class="w-full"
                                placeholder="Комментарий..."
                            />
                        </template>
                    </Column>
                    <Column header="Текущий статус">
                        <template #body="{ data }">
                            <Tag
                                v-if="data.attendance"
                                :value="statusLabels[data.attendance.status]"
                                :severity="statusSeverity[data.attendance.status]"
                            />
                        </template>
                    </Column>
                </DataTable>

                <div class="flex gap-2 mt-4">
                    <Button type="submit" label="Сохранить" :loading="form.processing" />
                    <Button type="button" label="Отмена" severity="secondary" @click="$inertia.visit(`/courses/${course.id}/lessons`)" />
                </div>
            </form>
        </div>
    </AppLayout>
</template>


