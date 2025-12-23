<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import ProgressBar from 'primevue/progressbar';
import Card from 'primevue/card';

const props = defineProps({
    employee: Object,
    report: Array,
});
</script>

<template>
    <AppLayout>
        <div class="max-w-7xl mx-auto">
            <div class="mb-6">
                <h1 class="text-2xl font-bold">Отчёт по сотруднику</h1>
                <p class="text-surface-500">{{ employee.name }} ({{ employee.email }})</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <Card>
                    <template #title>Курсов</template>
                    <template #content>
                        <p class="text-3xl font-bold">{{ report.length }}</p>
                    </template>
                </Card>
                <Card>
                    <template #title>Активных</template>
                    <template #content>
                        <p class="text-3xl font-bold">
                            {{ report.filter((r) => r.status === 'active').length }}
                        </p>
                    </template>
                </Card>
                <Card>
                    <template #title>Завершённых</template>
                    <template #content>
                        <p class="text-3xl font-bold">
                            {{ report.filter((r) => r.status === 'completed').length }}
                        </p>
                    </template>
                </Card>
            </div>

            <DataTable :value="report" stripedRows>
                <Column field="name" header="Курс" />
                <Column field="teacher" header="Преподаватель" />
                <Column field="status" header="Статус">
                    <template #body="{ data }">
                        <Tag
                            :value="data.status === 'active' ? 'Активный' : data.status === 'completed' ? 'Завершён' : 'Черновик'"
                            :severity="data.status === 'active' ? 'success' : data.status === 'completed' ? 'info' : 'secondary'"
                        />
                    </template>
                </Column>
                <Column field="start_date" header="Начало">
                    <template #body="{ data }">
                        {{ new Date(data.start_date).toLocaleDateString('ru-RU') }}
                    </template>
                </Column>
                <Column field="end_date" header="Окончание">
                    <template #body="{ data }">
                        {{ data.end_date ? new Date(data.end_date).toLocaleDateString('ru-RU') : '-' }}
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
                <Column header="Статистика">
                    <template #body="{ data }">
                        <div class="flex gap-2 text-xs">
                            <Tag :value="`✓ ${data.attendance.present}`" severity="success" />
                            <Tag :value="`✗ ${data.attendance.absent}`" severity="danger" />
                            <Tag :value="`~ ${data.attendance.late}`" severity="warning" />
                            <Tag :value="`О ${data.attendance.excused}`" severity="info" />
                        </div>
                    </template>
                </Column>
                <Column header="Оценки">
                    <template #body="{ data }">
                        <div v-if="data.grades.total > 0">
                            <Tag
                                :value="`${data.grades.passed}/${data.grades.total}`"
                                :severity="data.grades.passed / data.grades.total >= 0.5 ? 'success' : 'warning'"
                            />
                        </div>
                        <span v-else class="text-surface-500">-</span>
                    </template>
                </Column>
                <Column header="Итоговая оценка">
                    <template #body="{ data }">
                        <Tag
                            v-if="data.final_grade"
                            :value="data.final_grade"
                            :severity="data.final_grade === 'Зачёт' ? 'success' : 'danger'"
                        />
                        <span v-else class="text-surface-500">-</span>
                    </template>
                </Column>
            </DataTable>
        </div>
    </AppLayout>
</template>
