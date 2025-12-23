<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import ProgressBar from 'primevue/progressbar';

const props = defineProps({
    course: Object,
    lessons: Array,
    report: Array,
});
</script>

<template>
    <AppLayout>
        <div class="max-w-7xl mx-auto">
            <div class="mb-6">
                <h1 class="text-2xl font-bold">Отчёт по посещаемости</h1>
                <p class="text-surface-500">{{ course.name }}</p>
            </div>

            <DataTable :value="report" stripedRows>
                <Column field="name" header="Сотрудник" />
                <Column header="Присутствовал">
                    <template #body="{ data }">
                        <Tag :value="data.stats.present" severity="success" />
                    </template>
                </Column>
                <Column header="Отсутствовал">
                    <template #body="{ data }">
                        <Tag :value="data.stats.absent" severity="danger" />
                    </template>
                </Column>
                <Column header="Опоздал">
                    <template #body="{ data }">
                        <Tag :value="data.stats.late" severity="warning" />
                    </template>
                </Column>
                <Column header="Уважительная причина">
                    <template #body="{ data }">
                        <Tag :value="data.stats.excused" severity="info" />
                    </template>
                </Column>
                <Column header="Посещаемость">
                    <template #body="{ data }">
                        <div class="flex items-center gap-2">
                            <ProgressBar :value="data.attendance_rate" class="flex-1" />
                            <span class="text-sm font-medium">{{ data.attendance_rate }}%</span>
                        </div>
                    </template>
                </Column>
            </DataTable>
        </div>
    </AppLayout>
</template>


