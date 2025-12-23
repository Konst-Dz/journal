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
import Paginator from 'primevue/paginator';
import ConfirmDialog from 'primevue/confirmdialog';
import { useConfirm } from 'primevue/useconfirm';

const props = defineProps({
    users: Object,
    filters: Object,
});

const confirm = useConfirm();
const search = ref(props.filters.search || '');
const role = ref(props.filters.role || null);

const roles = [
    { label: 'Все роли', value: null },
    { label: 'Админ', value: 'admin' },
    { label: 'Преподаватель', value: 'teacher' },
    { label: 'Сотрудник', value: 'employee' },
];

const roleSeverity = {
    admin: 'danger',
    teacher: 'info',
    employee: 'success',
};

const roleLabels = {
    admin: 'Админ',
    teacher: 'Преподаватель',
    employee: 'Сотрудник',
};

let timeout;
watch([search, role], () => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get('/users', { search: search.value, role: role.value, page: 1 }, { preserveState: true });
    }, 300);
});

const onPageChange = (event) => {
    router.get('/users', {
        search: search.value,
        role: role.value,
        page: event.page + 1,
    }, { preserveState: true });
};

const deleteUser = (user) => {
    confirm.require({
        message: `Удалить пользователя "${user.name}"?`,
        header: 'Подтверждение',
        icon: 'pi pi-exclamation-triangle',
        acceptLabel: 'Удалить',
        rejectLabel: 'Отмена',
        accept: () => router.delete(`/users/${user.id}`),
    });
};
</script>

<template>
    <AppLayout>
        <ConfirmDialog />
        <div class="max-w-7xl mx-auto">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Пользователи</h1>
                <Button label="Добавить" icon="pi pi-plus" @click="router.visit('/users/create')" />
            </div>

            <div class="flex gap-4 mb-4">
                <InputText v-model="search" placeholder="Поиск..." class="w-64" />
                <Select v-model="role" :options="roles" optionLabel="label" optionValue="value" placeholder="Роль" class="w-48" />
            </div>

            <DataTable :value="users.data" stripedRows>
                <Column field="name" header="Имя" />
                <Column field="email" header="Email" />
                <Column field="role" header="Роль">
                    <template #body="{ data }">
                        <Tag :value="roleLabels[data.role]" :severity="roleSeverity[data.role]" />
                    </template>
                </Column>
                <Column header="Действия" style="width: 150px">
                    <template #body="{ data }">
                        <div class="flex gap-2">
                            <Button icon="pi pi-pencil" severity="info" text rounded @click="router.visit(`/users/${data.id}/edit`)" />
                            <Button icon="pi pi-trash" severity="danger" text rounded @click="deleteUser(data)" />
                        </div>
                    </template>
                </Column>
            </DataTable>

            <Paginator
                v-if="users.total > users.per_page"
                :rows="users.per_page"
                :totalRecords="users.total"
                :first="(users.current_page - 1) * users.per_page"
                @page="onPageChange"
                class="mt-4"
            />
        </div>
    </AppLayout>
</template>


