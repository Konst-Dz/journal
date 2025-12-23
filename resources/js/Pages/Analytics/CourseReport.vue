<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import ProgressBar from 'primevue/progressbar';
import Card from 'primevue/card';

const props = defineProps({
    course: Object,
    lessons: Array,
    report: Array,
});

const exportData = () => {
    window.location.href = `/courses/${props.course.id}/analytics/export?format=csv`;
};
</script>

<template>
    <AppLayout>
        <div class="max-w-7xl mx-auto">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl font-bold">Отчёт по курсу</h1>
                    <p class="text-surface-500">{{ course.name }}</p>
                </div>
                <Button label="Экспорт CSV" icon="pi pi-download" @click="exportData" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <Card>
                    <template #title>Студентов</template>
                    <template #content>
                        <p class="text-3xl font-bold">{{ report.length }}</p>
                    </template>
                </Card>
                <Card>
                    <template #title>Занятий</template>
                    <template #content>
                        <p class="text-3xl font-bold">{{ lessons.length }}</p>
                    </template>
                </Card>
                <Card>
                    <template #title>Средняя посещаемость</template>
                    <template #content>
                        <p class="text-3xl font-bold">
                            {{
                                report.length > 0
                                    ? Math.round(
                                          report.reduce((sum, r) => sum + r.attendance_rate, 0) / report.length
                                      )
                                    : 0
                            }}%
                        </p>
                    </template>
                </Card>
            </div>

            <DataTable :value="report" stripedRows>
                <Column field="name" header="Студент" />
                <Column field="email" header="Email" />
                <Column header="Посещаемость">
                    <template #body="{ data }">
                        <div class="flex items-center gap-2">
                            <ProgressBar :value="data.attendance_rate" class="flex-1" />
                            <span class="text-sm font-medium">{{ data.attendance_rate }}%</span>
                        </div>
                    </template>
                </Column>
                <Column header="Статистика посещаемости">
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
                                :severity="data.grades.rate >= 50 ? 'success' : 'warning'"
                            />
                            <span class="text-xs text-surface-500 ml-2">{{ data.grades.rate }}%</span>
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
