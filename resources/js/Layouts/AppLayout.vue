<script setup>
import { computed } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import Menubar from 'primevue/menubar';
import Avatar from 'primevue/avatar';
import Button from 'primevue/button';

const page = usePage();
const user = computed(() => page.props.auth.user);

const menuItems = computed(() => {
    const items = [
        { label: 'Главная', icon: 'pi pi-home', url: '/dashboard' },
    ];

    if (user.value?.role === 'employee') {
        items.push({ label: 'Личный кабинет', icon: 'pi pi-user', url: '/profile' });
    }

    if (user.value?.role === 'admin' || user.value?.role === 'teacher') {
        items.push({ label: 'Курсы', icon: 'pi pi-book', url: '/courses' });
    }

    if (user.value?.role === 'admin' || user.value?.role === 'teacher') {
        items.push({ label: 'Пользователи', icon: 'pi pi-users', url: '/users' });
    }

    if (user.value?.role === 'admin' || user.value?.role === 'teacher') {
        items.push({ label: 'Аналитика', icon: 'pi pi-chart-bar', url: '/analytics' });
    }

    return items;
});

const logout = () => {
    router.post('/logout');
};

const roleLabel = computed(() => {
    const roles = { admin: 'Админ', teacher: 'Преподаватель', employee: 'Сотрудник' };
    return roles[user.value?.role] || '';
});
</script>

<template>
    <div class="min-h-screen bg-surface-50">
        <Menubar :model="menuItems" class="rounded-none border-x-0 border-t-0">
            <template #start>
                <span class="font-bold text-xl mr-4">📚 Журнал</span>
            </template>
            <template #end>
                <div class="flex items-center gap-3">
                    <div class="text-right hidden sm:block">
                        <div class="font-medium">{{ user?.name }}</div>
                        <div class="text-xs text-surface-500">{{ roleLabel }}</div>
                    </div>
                    <Avatar :label="user?.name?.charAt(0)" shape="circle" />
                    <Button
                        icon="pi pi-sign-out"
                        severity="secondary"
                        text
                        rounded
                        @click="logout"
                        v-tooltip.bottom="'Выйти'"
                    />
                </div>
            </template>
        </Menubar>

        <main class="p-6">
            <slot />
        </main>
    </div>
</template>


