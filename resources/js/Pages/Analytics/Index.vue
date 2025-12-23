<script setup>
import { ref } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from 'primevue/card';
import TabView from 'primevue/tabview';
import TabPanel from 'primevue/tabpanel';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Chart from 'primevue/chart';

const props = defineProps({
    attendanceStats: Object,
    gradesStats: Object,
    userRatings: Array,
    teacherRatings: Array,
    courseRatings: Array,
    lessonRatings: Array,
    isAdmin: Boolean,
});

const attendanceChartData = ref({
    labels: ['Присутствовал', 'Отсутствовал', 'Опоздал', 'Уважительная'],
    datasets: [{
        data: [
            props.attendanceStats.present,
            props.attendanceStats.absent,
            props.attendanceStats.late,
            props.attendanceStats.excused,
        ],
        backgroundColor: ['#10b981', '#ef4444', '#f59e0b', '#3b82f6'],
    }],
});

const gradesChartData = ref({
    labels: ['Зачёт', 'Незачёт'],
    datasets: [{
        data: [props.gradesStats.passed, props.gradesStats.failed],
        backgroundColor: ['#10b981', '#ef4444'],
    }],
});

const chartOptions = ref({
    plugins: {
        legend: {
            position: 'bottom',
        },
    },
});
</script>

<template>
    <AppLayout>
        <div class="space-y-6">
            <div class="flex justify-between items-center">
                <h1 class="text-3xl font-bold">Аналитика</h1>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <Card>
                    <template #title>Статистика посещений</template>
                    <template #content>
                        <Chart type="pie" :data="attendanceChartData" :options="chartOptions" />
                        <div class="mt-4 space-y-2">
                            <div class="flex justify-between">
                                <span>Всего отметок:</span>
                                <strong>{{ attendanceStats.total }}</strong>
                            </div>
                        </div>
                    </template>
                </Card>

                <Card>
                    <template #title>Статистика оценок</template>
                    <template #content>
                        <Chart type="pie" :data="gradesChartData" :options="chartOptions" />
                        <div class="mt-4 space-y-2">
                            <div class="flex justify-between">
                                <span>Всего оценок:</span>
                                <strong>{{ gradesStats.total }}</strong>
                            </div>
                        </div>
                    </template>
                </Card>
            </div>

            <Card>
                <template #content>
                    <TabView>
                        <TabPanel header="Рейтинг пользователей">
                            <DataTable :value="userRatings" stripedRows>
                                <Column field="name" header="Имя" sortable />
                                <Column field="email" header="Email" sortable />
                                <Column field="total_lessons" header="Занятий" sortable />
                                <Column field="attendance_rate" header="Посещаемость" sortable>
                                    <template #body="{ data }">
                                        <span :class="{
                                            'text-green-600 font-semibold': data.attendance_rate >= 80,
                                            'text-orange-600 font-semibold': data.attendance_rate >= 50 && data.attendance_rate < 80,
                                            'text-red-600 font-semibold': data.attendance_rate < 50,
                                        }">
                                            {{ data.attendance_rate }}%
                                        </span>
                                    </template>
                                </Column>
                            </DataTable>
                        </TabPanel>

                        <TabPanel header="Рейтинг преподавателей" v-if="isAdmin">
                            <DataTable :value="teacherRatings" stripedRows>
                                <Column field="name" header="Имя" sortable />
                                <Column field="email" header="Email" sortable />
                                <Column field="courses_count" header="Курсов" sortable />
                                <Column field="students_count" header="Студентов" sortable />
                                <Column field="avg_attendance" header="Ср. посещаемость" sortable>
                                    <template #body="{ data }">
                                        <span :class="{
                                            'text-green-600 font-semibold': data.avg_attendance >= 80,
                                            'text-orange-600 font-semibold': data.avg_attendance >= 50 && data.avg_attendance < 80,
                                            'text-red-600 font-semibold': data.avg_attendance < 50,
                                        }">
                                            {{ data.avg_attendance }}%
                                        </span>
                                    </template>
                                </Column>
                            </DataTable>
                        </TabPanel>

                        <TabPanel header="Рейтинг курсов">
                            <DataTable :value="courseRatings" stripedRows>
                                <Column field="name" header="Курс" sortable />
                                <Column field="teacher" header="Преподаватель" sortable />
                                <Column field="students_count" header="Студентов" sortable />
                                <Column field="lessons_count" header="Занятий" sortable />
                                <Column field="attendance_rate" header="Посещаемость" sortable>
                                    <template #body="{ data }">
                                        <span :class="{
                                            'text-green-600 font-semibold': data.attendance_rate >= 80,
                                            'text-orange-600 font-semibold': data.attendance_rate >= 50 && data.attendance_rate < 80,
                                            'text-red-600 font-semibold': data.attendance_rate < 50,
                                        }">
                                            {{ data.attendance_rate }}%
                                        </span>
                                    </template>
                                </Column>
                            </DataTable>
                        </TabPanel>

                        <TabPanel header="Рейтинг занятий">
                            <DataTable :value="lessonRatings" stripedRows paginator :rows="10">
                                <Column field="title" header="Занятие" sortable />
                                <Column field="course" header="Курс" sortable />
                                <Column field="scheduled_at" header="Дата" sortable />
                                <Column field="students_count" header="Студентов" sortable />
                                <Column field="attendance_rate" header="Посещаемость" sortable>
                                    <template #body="{ data }">
                                        <span :class="{
                                            'text-green-600 font-semibold': data.attendance_rate >= 80,
                                            'text-orange-600 font-semibold': data.attendance_rate >= 50 && data.attendance_rate < 80,
                                            'text-red-600 font-semibold': data.attendance_rate < 50,
                                        }">
                                            {{ data.attendance_rate }}%
                                        </span>
                                    </template>
                                </Column>
                            </DataTable>
                        </TabPanel>
                    </TabView>
                </template>
            </Card>
        </div>
    </AppLayout>
</template>


