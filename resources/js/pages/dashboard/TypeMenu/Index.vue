<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { TableReusable, StatsCard } from '@/components/shared';
import type { TableColumn, TableAction, PaginationData } from '@/components/shared';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Switch } from '@/components/ui/switch';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Plus, ListOrdered, CheckCircle, XCircle, Search, Eye, Pencil, Trash2 } from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';
import type { MenuTypeIndexProps, MenuType } from '@menu/types';

const props = defineProps<MenuTypeIndexProps>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Menus', href: '/dashboard/menus' },
    { title: 'Menu Types', href: '/dashboard/menu-types' },
];

const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || 'all');

const columns: TableColumn<MenuType>[] = [
    {
        key: 'name',
        label: 'Type Name',
        render: (menuType) => menuType.name,
    },
    {
        key: 'description',
        label: 'Description',
        render: (menuType) => menuType.description || '-',
    },
    {
        key: 'sort_order',
        label: 'Sort Order',
        render: (menuType) => menuType.sort_order.toString(),
    },
    {
        key: 'status',
        label: 'Status',
        render: (menuType) => menuType.status ? 'Active' : 'Inactive',
    },
];

const actions: TableAction<MenuType>[] = [
    {
        label: 'View',
        icon: Eye,
        onClick: (menuType) => router.visit(`/dashboard/menu-types/${menuType.id}`),
    },
    {
        label: 'Edit',
        icon: Pencil,
        onClick: (menuType) => router.visit(`/dashboard/menu-types/${menuType.id}/edit`),
    },
    {
        label: 'Delete',
        icon: Trash2,
        onClick: (menuType) => router.visit(`/dashboard/menu-types/${menuType.id}/delete`),
        variant: 'destructive',
    },
];

const pagination = computed<PaginationData>(() => ({
    current_page: props.menuTypes.meta.current_page,
    last_page: props.menuTypes.meta.last_page,
    per_page: props.menuTypes.meta.per_page,
    total: props.menuTypes.meta.total,
}));

const handlePageChange = (page: number) => {
    router.get('/dashboard/menu-types', {
        page,
        per_page: pagination.value.per_page,
        search: search.value || undefined,
        status: statusFilter.value !== 'all' ? statusFilter.value : undefined,
    }, { preserveState: true });
};

const handlePerPageChange = (perPage: number) => {
    router.get('/dashboard/menu-types', {
        per_page: perPage,
        search: search.value || undefined,
        status: statusFilter.value !== 'all' ? statusFilter.value : undefined,
    }, { preserveState: true });
};

const handleSearch = () => {
    router.get('/dashboard/menu-types', {
        search: search.value || undefined,
        status: statusFilter.value !== 'all' ? statusFilter.value : undefined,
    }, { preserveState: true });
};

watch(statusFilter, () => {
    router.get('/dashboard/menu-types', {
        search: search.value || undefined,
        status: statusFilter.value !== 'all' ? statusFilter.value : undefined,
    }, { preserveState: true });
});

const handleCreate = () => {
    router.visit('/dashboard/menu-types/create');
};

const handleStatusToggle = (menuType: MenuType, newStatus: boolean) => {
    router.put(`/dashboard/menu-types/${menuType.id}/toggle-status`, {
        status: newStatus,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Menu Types" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Stats -->
            <div class="grid gap-4 md:grid-cols-3">
                <StatsCard
                    title="Total Types"
                    :value="props.stats.total"
                    :icon="ListOrdered"
                />
                <StatsCard
                    title="Active"
                    :value="props.stats.active"
                    :icon="CheckCircle"
                    variant="success"
                />
                <StatsCard
                    title="Inactive"
                    :value="props.stats.inactive"
                    :icon="XCircle"
                    variant="warning"
                />
            </div>

            <!-- Main Content -->
            <div class="flex flex-col gap-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold">Menu Types</h2>
                        <p class="text-sm text-muted-foreground">Manage your menu types</p>
                    </div>
                    <Button @click="handleCreate">
                        <Plus class="mr-2 h-4 w-4" />
                        Add Menu Type
                    </Button>
                </div>

                <!-- Filters -->
                <div class="flex items-center gap-4">
                    <div class="relative flex-1 max-w-sm">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                        <Input
                            v-model="search"
                            placeholder="Search menu types..."
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
                    :data="props.menuTypes.data"
                    :columns="columns"
                    :actions="actions"
                    :pagination="pagination"
                    :searchable="false"
                    @page-change="handlePageChange"
                    @per-page-change="handlePerPageChange"
                >
                    <template #cell-status="{ item }">
                        <div class="flex items-center gap-2" @click.stop>
                            <Switch
                                :model-value="item.status"
                                @update:model-value="handleStatusToggle(item, $event)"
                            />
                            <span class="text-sm text-muted-foreground">
                                {{ item.status ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </template>
                </TableReusable>
            </div>
        </div>
    </AppLayout>
</template>
