<script setup>
import { useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Dialog from 'primevue/dialog';
import InputSwitch from 'primevue/inputswitch';
import Textarea from 'primevue/textarea';
import { ref } from 'vue';

const props = defineProps({
    course: Object,
    lessons: Array,
    gradebook: Array,
});

const form = useForm({
    user_id: null,
    lesson_id: null,
    passed: false,
    comment: '',
});

const dialogVisible = ref(false);
const editingGrade = ref(null);
const editingStudent = ref(null);
const editingLesson = ref(null);

const openDialog = (student, lesson = null) => {
    editingStudent.value = student;
    editingLesson.value = lesson;
    
    if (lesson) {
        const grade = student.lessons.find(l => l.lesson_id === lesson.id)?.grade;
        editingGrade.value = grade;
        form.user_id = student.id;
        form.lesson_id = lesson.id;
        form.passed = grade?.passed || false;
        form.comment = grade?.comment || '';
    } else {
        editingGrade.value = student.final;
        form.user_id = student.id;
        form.lesson_id = null;
        form.passed = student.final?.passed || false;
        form.comment = student.final?.comment || '';
    }
    
    dialogVisible.value = true;
};

const submit = () => {
    form.post(`/courses/${props.course.id}/grades`, {
        onSuccess: () => {
            dialogVisible.value = false;
            form.reset();
        },
    });
};

const deleteGrade = (gradeId) => {
    if (confirm('Удалить оценку?')) {
        router.delete(`/courses/${props.course.id}/grades/${gradeId}`, {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <AppLayout>
        <div class="max-w-7xl mx-auto">
            <div class="mb-6">
                <h1 class="text-2xl font-bold">Журнал успеваемости</h1>
                <p class="text-surface-500">{{ course.name }}</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="bg-surface-100">
                            <th class="border p-2 text-left sticky left-0 bg-surface-100 z-10">Студент</th>
                            <th
                                v-for="lesson in lessons"
                                :key="lesson.id"
                                class="border p-2 text-center min-w-[120px]"
                            >
                                <div class="text-xs">{{ new Date(lesson.scheduled_at).toLocaleDateString('ru-RU') }}</div>
                                <div class="text-xs text-surface-500">{{ lesson.title }}</div>
                            </th>
                            <th class="border p-2 text-center bg-primary-50 min-w-[120px]">Итог</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="student in gradebook" :key="student.id" class="hover:bg-surface-50">
                            <td class="border p-2 sticky left-0 bg-white z-10 font-medium">{{ student.name }}</td>
                            <td
                                v-for="lesson in lessons"
                                :key="lesson.id"
                                class="border p-2 text-center"
                            >
                                <div v-if="student.lessons.find(l => l.lesson_id === lesson.id)?.grade">
                                    <Tag
                                        :value="student.lessons.find(l => l.lesson_id === lesson.id)?.grade.passed ? 'Зачёт' : 'Незачёт'"
                                        :severity="student.lessons.find(l => l.lesson_id === lesson.id)?.grade.passed ? 'success' : 'danger'"
                                        class="mb-1"
                                    />
                                    <Button
                                        icon="pi pi-pencil"
                                        size="small"
                                        text
                                        rounded
                                        @click="openDialog(student, lesson)"
                                        class="ml-1"
                                    />
                                </div>
                                <Button
                                    v-else
                                    icon="pi pi-plus"
                                    size="small"
                                    text
                                    rounded
                                    @click="openDialog(student, lesson)"
                                />
                            </td>
                            <td class="border p-2 text-center bg-primary-50">
                                <div v-if="student.final">
                                    <Tag
                                        :value="student.final.passed ? 'Зачёт' : 'Незачёт'"
                                        :severity="student.final.passed ? 'success' : 'danger'"
                                        class="mb-1"
                                    />
                                    <Button
                                        icon="pi pi-pencil"
                                        size="small"
                                        text
                                        rounded
                                        @click="openDialog(student)"
                                        class="ml-1"
                                    />
                                </div>
                                <Button
                                    v-else
                                    icon="pi pi-plus"
                                    size="small"
                                    text
                                    rounded
                                    @click="openDialog(student)"
                                />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <Dialog v-model:visible="dialogVisible" modal header="Оценка" :style="{ width: '500px' }">
                <form @submit.prevent="submit" class="flex flex-col gap-4">
                    <div class="flex flex-col gap-2">
                        <label>Студент</label>
                        <InputText :value="editingStudent?.name" disabled class="w-full" />
                    </div>

                    <div v-if="editingLesson" class="flex flex-col gap-2">
                        <label>Занятие</label>
                        <InputText :value="editingLesson?.title" disabled class="w-full" />
                    </div>

                    <div v-else class="flex flex-col gap-2">
                        <label>Итоговая оценка</label>
                    </div>

                    <div class="flex items-center gap-2">
                        <InputSwitch v-model="form.passed" />
                        <label>{{ form.passed ? 'Зачёт' : 'Незачёт' }}</label>
                    </div>

                    <div class="flex flex-col gap-2">
                        <label for="comment">Комментарий</label>
                        <Textarea id="comment" v-model="form.comment" rows="3" class="w-full" />
                    </div>

                    <div class="flex gap-2 mt-4">
                        <Button type="submit" label="Сохранить" :loading="form.processing" />
                        <Button
                            v-if="editingGrade"
                            type="button"
                            label="Удалить"
                            severity="danger"
                            @click="deleteGrade(editingGrade.id); dialogVisible = false"
                        />
                        <Button
                            type="button"
                            label="Отмена"
                            severity="secondary"
                            @click="dialogVisible = false"
                        />
                    </div>
                </form>
            </Dialog>
        </div>
    </AppLayout>
</template>
