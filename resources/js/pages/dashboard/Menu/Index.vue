<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { TableReusable, StatsCard } from '@/components/shared';
import type { TableColumn, TableAction, PaginationData } from '@/components/shared';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Plus, UtensilsCrossed, CheckCircle, XCircle, Search, Eye, Pencil, Trash2 } from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';
import type { MenuIndexProps, Menu } from '../../../types';

const props = defineProps<MenuIndexProps>();

const { menus, filters, stats } = props;

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Menus', href: '/dashboard/menus' },
];

const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || 'all');

const columns: TableColumn<Menu>[] = [
    {
        key: 'name',
        label: 'Menu Name',
        render: (menu) => menu.name,
    },
    {
        key: 'description',
        label: 'Description',
        render: (menu) => menu.description || '-',
    },
    {
        key: 'status',
        label: 'Status',
        render: (menu) => menu.status ? 'Active' : 'Inactive',
    },
];

const actions: TableAction<Menu>[] = [
    {
        label: 'View',
        icon: Eye,
        onClick: (menu) => router.visit(`/dashboard/menus/${menu.id}`),
    },
    {
        label: 'Edit',
        icon: Pencil,
        onClick: (menu) => router.visit(`/dashboard/menus/${menu.id}/edit`),
    },
    {
        label: 'Delete',
        icon: Trash2,
        onClick: (menu) => router.visit(`/dashboard/menus/${menu.id}/delete`),
        variant: 'destructive',
    },
];

const pagination: PaginationData = {
    current_page: props.menus.meta.current_page,
    last_page: props.menus.meta.last_page,
    per_page: props.menus.meta.per_page,
    total: props.menus.meta.total,
};

const handlePageChange = (page: number) => {
    router.get('/dashboard/menus', {
        page,
        per_page: pagination.per_page,
        search: search.value || undefined,
        status: statusFilter.value !== 'all' ? statusFilter.value : undefined,
    }, { preserveState: true });
};

const handlePerPageChange = (perPage: number) => {
    router.get('/dashboard/menus', {
        per_page: perPage,
        search: search.value || undefined,
        status: statusFilter.value !== 'all' ? statusFilter.value : undefined,
    }, { preserveState: true });
};

const handleSearch = () => {
    router.get('/dashboard/menus', {
        search: search.value || undefined,
        status: statusFilter.value !== 'all' ? statusFilter.value : undefined,
    }, { preserveState: true });
};

watch(statusFilter, () => {
    router.get('/dashboard/menus', {
        search: search.value || undefined,
        status: statusFilter.value !== 'all' ? statusFilter.value : undefined,
    }, { preserveState: true });
});

const handleCreate = () => {
    router.visit('/dashboard/menus/create');
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Menus" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Stats -->
            <div class="grid gap-4 md:grid-cols-3">
                <StatsCard
                    title="Total Menus"
                    :value="stats.total"
                    :icon="UtensilsCrossed"
                />
                <StatsCard
                    title="Active"
                    :value="stats.active"
                    :icon="CheckCircle"
                    variant="success"
                />
                <StatsCard
                    title="Inactive"
                    :value="stats.inactive"
                    :icon="XCircle"
                    variant="warning"
                />
            </div>

            <!-- Main Content -->
            <div class="flex flex-col gap-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold">Menus</h2>
                        <p class="text-sm text-muted-foreground">Manage your menus</p>
                    </div>
                    <Button @click="handleCreate">
                        <Plus class="mr-2 h-4 w-4" />
                        Add Menu
                    </Button>
                </div>

                <!-- Filters -->
                <div class="flex items-center gap-4">
                    <div class="relative flex-1 max-w-sm">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                        <Input
                            v-model="search"
                            placeholder="Search menus..."
                            class="pl-9"
                            @keyup.enter="handleSearch"
                        />
                    </div>
                    <Select v-model="statusFilter">
                        <SelectTrigger class="w-[150px]">
                            <SelectValue placeholder="Status" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All Status</SelectItem>
                            <SelectItem value="1">Active</SelectItem>
                            <SelectItem value="0">Inactive</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <!-- Table -->
                <TableReusable
                    :data="menus.data"
                    :columns="columns"
                    :actions="actions"
                    :pagination="pagination"
                    :searchable="false"
                    @page-change="handlePageChange"
                    @per-page-change="handlePerPageChange"
                />
            </div>
        </div>
    </AppLayout>
</template>
