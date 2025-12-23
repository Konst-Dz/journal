<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from 'primevue/card';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import ProgressBar from 'primevue/progressbar';
import Tag from 'primevue/tag';

const props = defineProps({
    schedule: Array,
    attendances: Array,
    attendanceStats: Object,
    grades: Array,
    gradeStats: Object,
});

const page = usePage();
const user = computed(() => page.props.auth.user);

const lessonTypeSeverity = {
    lecture: 'info',
    practice: 'success',
    seminar: 'warn',
    exam: 'danger',
};

const lessonTypeLabels = {
    lecture: 'Лекция',
    practice: 'Практика',
    seminar: 'Семинар',
    exam: 'Экзамен',
};

const attendanceStatusSeverity = {
    present: 'success',
    absent: 'danger',
    late: 'warn',
};

const attendanceStatusLabels = {
    present: 'Присутствовал',
    absent: 'Отсутствовал',
    late: 'Опоздал',
};

const gradesSeverity = computed(() => {
    if (!props.gradeStats) return 'secondary';
    const percentage = props.gradeStats.percentage;
    if (percentage >= 80) return 'success';
    if (percentage >= 60) return 'warn';
    return 'danger';
});

const attendanceSeverity = computed(() => {
    if (!props.attendanceStats) return 'secondary';
    const percentage = props.attendanceStats.percentage;
    if (percentage >= 80) return 'success';
    if (percentage >= 60) return 'warn';
    return 'danger';
});
</script>

<template>
    <AppLayout>
        <div class="max-w-7xl mx-auto">
            <h1 class="text-2xl font-bold mb-6">Личный кабинет</h1>

            <!-- Статистика -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <Card>
                    <template #title>
                        <div class="flex items-center gap-2">
                            <i class="pi pi-check-circle text-green-500"></i>
                            Посещаемость
                        </div>
                    </template>
                    <template #content>
                        <div v-if="attendanceStats.total > 0" class="space-y-4">
                            <div class="grid grid-cols-3 gap-2 text-center">
                                <div class="p-3 bg-green-50 rounded">
                                    <div class="text-2xl font-bold text-green-600">{{ attendanceStats.present }}</div>
                                    <div class="text-sm text-surface-500">Присутствовал</div>
                                </div>
                                <div class="p-3 bg-red-50 rounded">
                                    <div class="text-2xl font-bold text-red-600">{{ attendanceStats.absent }}</div>
                                    <div class="text-sm text-surface-500">Отсутствовал</div>
                                </div>
                                <div class="p-3 bg-yellow-50 rounded">
                                    <div class="text-2xl font-bold text-yellow-600">{{ attendanceStats.late }}</div>
                                    <div class="text-sm text-surface-500">Опоздал</div>
                                </div>
                            </div>
                            <div>
                                <div class="flex justify-between mb-2">
                                    <span>Общая посещаемость</span>
                                    <span class="font-bold">{{ attendanceStats.percentage }}%</span>
                                </div>
                                <ProgressBar 
                                    :value="attendanceStats.percentage" 
                                    :severity="attendanceSeverity"
                                    :showValue="false"
                                />
                            </div>
                        </div>
                        <div v-else class="text-center text-surface-500 py-8">
                            <i class="pi pi-check-circle text-4xl mb-2"></i>
                            <p>Нет данных о посещаемости</p>
                        </div>
                    </template>
                </Card>

                <Card>
                    <template #title>
                        <div class="flex items-center gap-2">
                            <i class="pi pi-star text-yellow-500"></i>
                            Успеваемость
                        </div>
                    </template>
                    <template #content>
                        <div v-if="gradeStats.total > 0" class="space-y-4">
                            <div class="grid grid-cols-2 gap-2 text-center">
                                <div class="p-3 bg-green-50 rounded">
                                    <div class="text-2xl font-bold text-green-600">{{ gradeStats.passed }}</div>
                                    <div class="text-sm text-surface-500">Сдано</div>
                                </div>
                                <div class="p-3 bg-red-50 rounded">
                                    <div class="text-2xl font-bold text-red-600">{{ gradeStats.failed }}</div>
                                    <div class="text-sm text-surface-500">Не сдано</div>
                                </div>
                            </div>
                            <div>
                                <div class="flex justify-between mb-2">
                                    <span>Процент успеваемости</span>
                                    <span class="font-bold">{{ gradeStats.percentage }}%</span>
                                </div>
                                <ProgressBar 
                                    :value="gradeStats.percentage" 
                                    :severity="gradesSeverity"
                                    :showValue="false"
                                />
                            </div>
                        </div>
                        <div v-else class="text-center text-surface-500 py-8">
                            <i class="pi pi-star text-4xl mb-2"></i>
                            <p>Нет данных об оценках</p>
                        </div>
                    </template>
                </Card>
            </div>

            <!-- Расписание занятий -->
            <Card class="mb-6">
                <template #title>
                    <div class="flex items-center gap-2">
                        <i class="pi pi-calendar text-blue-500"></i>
                        Расписание занятий
                    </div>
                </template>
                <template #content>
                    <DataTable 
                        v-if="schedule && schedule.length > 0" 
                        :value="schedule" 
                        stripedRows
                        size="small"
                    >
                        <Column field="scheduled_at" header="Дата/Время" style="width: 180px">
                            <template #body="{ data }">
                                {{ new Date(data.scheduled_at).toLocaleString('ru-RU', { 
                                    day: '2-digit', 
                                    month: '2-digit', 
                                    year: 'numeric',
                                    hour: '2-digit', 
                                    minute: '2-digit' 
                                }) }}
                            </template>
                        </Column>
                        <Column field="title" header="Занятие" />
                        <Column field="course.name" header="Курс" />
                        <Column field="course.teacher.name" header="Преподаватель" />
                        <Column field="type" header="Тип" style="width: 120px">
                            <template #body="{ data }">
                                <Tag 
                                    :value="lessonTypeLabels[data.type]" 
                                    :severity="lessonTypeSeverity[data.type]"
                                    size="small"
                                />
                            </template>
                        </Column>
                        <Column field="duration_minutes" header="Длительность" style="width: 120px">
                            <template #body="{ data }">
                                {{ data.duration_minutes }} мин
                            </template>
                        </Column>
                    </DataTable>
                    <div v-else class="text-center text-surface-500 py-8">
                        <i class="pi pi-calendar text-4xl mb-2"></i>
                        <p>Нет запланированных занятий</p>
                    </div>
                </template>
            </Card>

            <!-- История посещений -->
            <Card class="mb-6">
                <template #title>
                    <div class="flex items-center gap-2">
                        <i class="pi pi-history text-purple-500"></i>
                        История посещений
                    </div>
                </template>
                <template #content>
                    <DataTable 
                        v-if="attendances && attendances.length > 0" 
                        :value="attendances" 
                        stripedRows
                        size="small"
                        paginator
                        :rows="10"
                    >
                        <Column field="lesson.scheduled_at" header="Дата" style="width: 150px">
                            <template #body="{ data }">
                                {{ new Date(data.lesson.scheduled_at).toLocaleDateString('ru-RU') }}
                            </template>
                        </Column>
                        <Column field="lesson.title" header="Занятие" />
                        <Column field="lesson.course.name" header="Курс" />
                        <Column field="status" header="Статус" style="width: 150px">
                            <template #body="{ data }">
                                <Tag 
                                    :value="attendanceStatusLabels[data.status]" 
                                    :severity="attendanceStatusSeverity[data.status]"
                                    size="small"
                                />
                            </template>
                        </Column>
                        <Column field="note" header="Примечание">
                            <template #body="{ data }">
                                <span class="text-sm text-surface-500">{{ data.note || '-' }}</span>
                            </template>
                        </Column>
                    </DataTable>
                    <div v-else class="text-center text-surface-500 py-8">
                        <i class="pi pi-history text-4xl mb-2"></i>
                        <p>Нет истории посещений</p>
                    </div>
                </template>
            </Card>

            <!-- История оценок -->
            <Card>
                <template #title>
                    <div class="flex items-center gap-2">
                        <i class="pi pi-list text-indigo-500"></i>
                        История оценок
                    </div>
                </template>
                <template #content>
                    <DataTable 
                        v-if="grades && grades.length > 0" 
                        :value="grades" 
                        stripedRows
                        size="small"
                        paginator
                        :rows="10"
                    >
                        <Column field="lesson.scheduled_at" header="Дата" style="width: 150px">
                            <template #body="{ data }">
                                {{ new Date(data.lesson.scheduled_at).toLocaleDateString('ru-RU') }}
                            </template>
                        </Column>
                        <Column field="lesson.title" header="Занятие" />
                        <Column field="course.name" header="Курс" />
                        <Column field="passed" header="Результат" style="width: 120px">
                            <template #body="{ data }">
                                <Tag 
                                    :value="data.passed ? 'Сдано' : 'Не сдано'" 
                                    :severity="data.passed ? 'success' : 'danger'"
                                    size="small"
                                />
                            </template>
                        </Column>
                        <Column field="comment" header="Комментарий">
                            <template #body="{ data }">
                                <span class="text-sm text-surface-500">{{ data.comment || '-' }}</span>
                            </template>
                        </Column>
                    </DataTable>
                    <div v-else class="text-center text-surface-500 py-8">
                        <i class="pi pi-list text-4xl mb-2"></i>
                        <p>Нет истории оценок</p>
                    </div>
                </template>
            </Card>
        </div>
    </AppLayout>
</template>


