<script setup>
import { computed, ref } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from 'primevue/card';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import ProgressBar from 'primevue/progressbar';
import DatePicker from 'primevue/datepicker';
import Tag from 'primevue/tag';
import Button from 'primevue/button';

const props = defineProps({
    metrics: {
        type: Object,
        default: () => ({})
    },
    upcoming_lessons: {
        type: Array,
        default: () => []
    },
    attendance_stats: {
        type: Object,
        default: () => ({ total: 0, present: 0, percentage: 0 })
    },
    calendar_data: {
        type: Object,
        default: () => ({})
    },
    top_lists: {
        type: Object,
        default: () => ({})
    },
});

const page = usePage();
const user = computed(() => page.props.auth.user);

const selectedDate = ref(null);
const selectedDateLessons = computed(() => {
    if (!selectedDate.value) return null;
    
    const dateObj = selectedDate.value instanceof Date ? selectedDate.value : new Date(selectedDate.value);
    const year = dateObj.getFullYear();
    const month = String(dateObj.getMonth() + 1).padStart(2, '0');
    const day = String(dateObj.getDate()).padStart(2, '0');
    const dateStr = `${year}-${month}-${day}`;
    
    return props.calendar_data[dateStr] || null;
});

const dateClass = (date) => {
    if (!date || !props.calendar_data) return '';
    
    // Конвертируем в Date если это не Date объект
    const dateObj = date instanceof Date ? date : new Date(date);
    
    const year = dateObj.getFullYear();
    const month = String(dateObj.getMonth() + 1).padStart(2, '0');
    const day = String(dateObj.getDate()).padStart(2, '0');
    const dateStr = `${year}-${month}-${day}`;
    
    if (props.calendar_data[dateStr]) {
        return 'has-lessons';
    }
    return '';
};

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

const lessonStatusSeverity = {
    scheduled: 'secondary',
    in_progress: 'warn',
    completed: 'success',
    cancelled: 'danger',
};

const lessonStatusLabels = {
    scheduled: 'Запланировано',
    in_progress: 'В процессе',
    completed: 'Завершено',
    cancelled: 'Отменено',
};

const attendanceSeverity = computed(() => {
    if (!props.attendance_stats) return 'secondary';
    const percentage = props.attendance_stats.percentage;
    if (percentage >= 80) return 'success';
    if (percentage >= 60) return 'warn';
    return 'danger';
});
</script>

<template>
    <AppLayout>
        <div class="max-w-7xl mx-auto">
            <h1 class="text-2xl font-bold mb-6">Добро пожаловать, {{ user?.name }}!</h1>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <Card>
                    <template #title>
                        <div class="flex items-center gap-2">
                            <i class="pi pi-book text-primary-500"></i>
                            Курсы
                        </div>
                    </template>
                    <template #content>
                        <p class="text-3xl font-bold">{{ metrics.active_courses }}</p>
                        <p class="text-surface-500">активных курсов</p>
                    </template>
                </Card>

                <Card>
                    <template #title>
                        <div class="flex items-center gap-2">
                            <i class="pi pi-calendar text-green-500"></i>
                            Занятия
                        </div>
                    </template>
                    <template #content>
                        <p class="text-3xl font-bold">{{ metrics.lessons_this_week }}</p>
                        <p class="text-surface-500">на этой неделе</p>
                    </template>
                </Card>

                <Card>
                    <template #title>
                        <div class="flex items-center gap-2">
                            <i class="pi pi-users text-orange-500"></i>
                            Сотрудники
                        </div>
                    </template>
                    <template #content>
                        <p class="text-3xl font-bold">{{ metrics.students_in_training }}</p>
                        <p class="text-surface-500">в обучении</p>
                    </template>
                </Card>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
                <!-- Календарь занятий -->
                <Card>
                    <template #title>
                        <div class="flex items-center gap-2">
                            <i class="pi pi-calendar-plus text-blue-500"></i>
                            Календарь
                        </div>
                    </template>
                    <template #content>
                        <DatePicker 
                            v-model="selectedDate" 
                            inline 
                            :pt="{
                                day: (options) => ({
                                    class: dateClass(options.context.date)
                                })
                            }"
                        />
                        
                        <div v-if="selectedDateLessons" class="mt-4 pt-4 border-t">
                            <h3 class="font-semibold mb-2">
                                Занятий: {{ selectedDateLessons.count }}
                            </h3>
                            <div class="space-y-2">
                                <div 
                                    v-for="lesson in selectedDateLessons.lessons" 
                                    :key="lesson.id"
                                    class="text-sm p-2 bg-surface-50 rounded"
                                >
                                    <div class="font-medium">{{ lesson.time }} - {{ lesson.title }}</div>
                                    <div class="text-surface-500">{{ lesson.course_name }}</div>
                                </div>
                            </div>
                        </div>
                    </template>
                </Card>

                <!-- Ближайшие занятия -->
                <Card class="lg:col-span-2">
                    <template #title>
                        <div class="flex items-center gap-2">
                            <i class="pi pi-clock text-purple-500"></i>
                            Ближайшие занятия
                        </div>
                    </template>
                    <template #content>
                        <DataTable 
                            v-if="upcoming_lessons && upcoming_lessons.length > 0" 
                            :value="upcoming_lessons" 
                            stripedRows
                            size="small"
                        >
                            <Column field="scheduled_at" header="Дата/Время" style="width: 180px">
                                <template #body="{ data }">
                                    {{ new Date(data.scheduled_at).toLocaleString('ru-RU', { 
                                        day: '2-digit', 
                                        month: '2-digit', 
                                        hour: '2-digit', 
                                        minute: '2-digit' 
                                    }) }}
                                </template>
                            </Column>
                            <Column field="title" header="Название" />
                            <Column field="course.name" header="Курс" />
                            <Column field="type" header="Тип" style="width: 120px">
                                <template #body="{ data }">
                                    <Tag 
                                        :value="lessonTypeLabels[data.type]" 
                                        :severity="lessonTypeSeverity[data.type]"
                                        size="small"
                                    />
                                </template>
                            </Column>
                            <Column header="Действия" style="width: 100px">
                                <template #body="{ data }">
                                    <Button 
                                        icon="pi pi-eye" 
                                        severity="info" 
                                        text 
                                        rounded
                                        size="small"
                                        @click="router.visit(`/courses/${data.course.id}`)" 
                                    />
                                </template>
                            </Column>
                        </DataTable>
                        <div v-else class="text-center text-surface-500 py-8">
                            <i class="pi pi-calendar text-4xl mb-2"></i>
                            <p>Нет запланированных занятий</p>
                        </div>
                    </template>
                </Card>
            </div>

            <!-- Статистика посещаемости -->
            <Card v-if="attendance_stats">
                <template #title>
                    <div class="flex items-center gap-2">
                        <i class="pi pi-chart-line text-teal-500"></i>
                        Посещаемость ваших курсов
                    </div>
                </template>
                <template #content>
                    <div v-if="attendance_stats.total > 0" class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-lg">
                                {{ attendance_stats.present }} посещений из {{ attendance_stats.total }} возможных
                            </span>
                            <span class="text-2xl font-bold">{{ attendance_stats.percentage }}%</span>
                        </div>
                        <ProgressBar 
                            :value="attendance_stats.percentage" 
                            :severity="attendanceSeverity"
                            :showValue="false"
                        />
                        <p class="text-sm text-surface-500">
                            <template v-if="attendance_stats.percentage >= 80">
                                Отличная посещаемость! Продолжайте в том же духе.
                            </template>
                            <template v-else-if="attendance_stats.percentage >= 60">
                                Хорошая посещаемость, но есть куда стремиться.
                            </template>
                            <template v-else>
                                Низкая посещаемость. Рекомендуем уделить больше внимания занятиям.
                            </template>
                        </p>
                    </div>
                    <div v-else class="text-center text-surface-500 py-8">
                        <i class="pi pi-chart-line text-4xl mb-2"></i>
                        <p>Нет данных о посещаемости</p>
                    </div>
                </template>
            </Card>

            <!-- Быстрые действия -->
            <Card v-if="user?.role !== 'employee'" class="mt-6">
                <template #title>
                    <div class="flex items-center gap-2">
                        <i class="pi pi-bolt text-yellow-500"></i>
                        Быстрые действия
                    </div>
                </template>
                <template #content>
                    <div class="flex flex-wrap gap-3">
                        <Button 
                            label="Создать курс" 
                            icon="pi pi-plus" 
                            @click="router.visit('/courses/create')"
                            severity="success"
                        />
                        <Button 
                            label="Посмотреть расписание" 
                            icon="pi pi-calendar" 
                            @click="router.visit('/lessons')"
                            severity="info"
                        />
                        <Button 
                            label="Все курсы" 
                            icon="pi pi-book" 
                            @click="router.visit('/courses')"
                        />
                        <Button 
                            v-if="user?.role === 'admin'" 
                            label="Управление пользователями" 
                            icon="pi pi-users" 
                            @click="router.visit('/users')"
                            severity="secondary"
                        />
                    </div>
                </template>
            </Card>

            <!-- Топ-5 списки -->
            <div v-if="top_lists && Object.keys(top_lists).length > 0" class="grid grid-cols-1 lg:grid-cols-2 gap-4 mt-6">
                <!-- Топ активных сотрудников -->
                <Card v-if="top_lists.top_students && top_lists.top_students.length > 0">
                    <template #title>
                        <div class="flex items-center gap-2">
                            <i class="pi pi-star text-amber-500"></i>
                            Топ сотрудников по посещаемости
                        </div>
                    </template>
                    <template #content>
                        <div class="space-y-3">
                            <div 
                                v-for="(student, index) in top_lists.top_students" 
                                :key="student.id"
                                class="flex items-center justify-between p-3 bg-surface-50 rounded hover:bg-surface-100 transition-colors"
                            >
                                <div class="flex items-center gap-3">
                                    <div 
                                        class="w-8 h-8 rounded-full flex items-center justify-center font-bold"
                                        :class="{
                                            'bg-amber-500 text-white': index === 0,
                                            'bg-slate-400 text-white': index === 1,
                                            'bg-orange-600 text-white': index === 2,
                                            'bg-surface-200 text-surface-700': index > 2
                                        }"
                                    >
                                        {{ index + 1 }}
                                    </div>
                                    <div>
                                        <div class="font-medium">{{ student.name }}</div>
                                        <div class="text-sm text-surface-500">
                                            {{ student.attendance_count }} посещений
                                        </div>
                                    </div>
                                </div>
                                <Tag 
                                    :value="student.attendance_percentage + '%'" 
                                    :severity="student.attendance_percentage >= 80 ? 'success' : student.attendance_percentage >= 60 ? 'warn' : 'danger'"
                                />
                            </div>
                        </div>
                    </template>
                </Card>

                <!-- Топ популярных курсов -->
                <Card v-if="top_lists.top_courses && top_lists.top_courses.length > 0">
                    <template #title>
                        <div class="flex items-center gap-2">
                            <i class="pi pi-trophy text-blue-500"></i>
                            Топ курсов по популярности
                        </div>
                    </template>
                    <template #content>
                        <div class="space-y-3">
                            <div 
                                v-for="(course, index) in top_lists.top_courses" 
                                :key="course.id"
                                class="flex items-center justify-between p-3 bg-surface-50 rounded hover:bg-surface-100 transition-colors cursor-pointer"
                                @click="router.visit(`/courses/${course.id}`)"
                            >
                                <div class="flex items-center gap-3">
                                    <div 
                                        class="w-8 h-8 rounded-full flex items-center justify-center font-bold"
                                        :class="{
                                            'bg-amber-500 text-white': index === 0,
                                            'bg-slate-400 text-white': index === 1,
                                            'bg-orange-600 text-white': index === 2,
                                            'bg-surface-200 text-surface-700': index > 2
                                        }"
                                    >
                                        {{ index + 1 }}
                                    </div>
                                    <div>
                                        <div class="font-medium">{{ course.name }}</div>
                                        <div class="text-sm text-surface-500">
                                            {{ course.students_count }} студентов
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </Card>

                <!-- Топ преподавателей (только для админа) -->
                <Card v-if="top_lists.top_teachers && top_lists.top_teachers.length > 0" class="lg:col-span-2">
                    <template #title>
                        <div class="flex items-center gap-2">
                            <i class="pi pi-graduation-cap text-indigo-500"></i>
                            Топ преподавателей по нагрузке
                        </div>
                    </template>
                    <template #content>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-3">
                            <div 
                                v-for="(teacher, index) in top_lists.top_teachers" 
                                :key="teacher.id"
                                class="p-4 bg-surface-50 rounded hover:bg-surface-100 transition-colors"
                            >
                                <div class="flex flex-col items-center text-center">
                                    <div 
                                        class="w-12 h-12 rounded-full flex items-center justify-center font-bold mb-2"
                                        :class="{
                                            'bg-amber-500 text-white': index === 0,
                                            'bg-slate-400 text-white': index === 1,
                                            'bg-orange-600 text-white': index === 2,
                                            'bg-surface-200 text-surface-700': index > 2
                                        }"
                                    >
                                        {{ index + 1 }}
                                    </div>
                                    <div class="font-medium mb-1">{{ teacher.name }}</div>
                                    <div class="text-sm text-surface-500">
                                        {{ teacher.courses_count }} курсов
                                    </div>
                                    <div class="text-sm text-surface-500">
                                        {{ teacher.students_count }} студентов
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
:deep(.has-lessons) {
    background-color: var(--p-primary-color) !important;
    color: white !important;
    font-weight: 600;
}
</style>
