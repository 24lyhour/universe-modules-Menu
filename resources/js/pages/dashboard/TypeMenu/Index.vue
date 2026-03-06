<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { TableReusable, StatsCard, ButtonGroup } from '@/components/shared';
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
import { Plus, ListOrdered, CheckCircle, XCircle, Search, Eye, Pencil, Trash2, Download, Upload, Database, X } from 'lucide-vue-next';
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

// Check if any filters are active
const hasActiveFilters = computed(() => {
    return !!(
        search.value ||
        (statusFilter.value !== 'all' && statusFilter.value !== '')
    );
});

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
        separator: true,
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

const handleExport = () => {
    const params = new URLSearchParams();
    if (search.value) params.append('search', search.value);
    if (statusFilter.value && statusFilter.value !== 'all') params.append('status', statusFilter.value);
    window.location.href = `/dashboard/menu-types/export?${params.toString()}`;
};

const handleClearFilters = () => {
    search.value = '';
    statusFilter.value = 'all';
    router.get('/dashboard/menu-types', {}, { preserveState: true, preserveScroll: true });
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
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Menu Types</h1>
                    <p class="text-muted-foreground">Manage your menu types</p>
                </div>
                <div class="flex items-center gap-2">
                    <ButtonGroup>
                        <Button variant="default">
                            <Database class="mr-2 h-4 w-4" />
                            All
                        </Button>
                        <Button variant="outline" as-child>
                            <Link href="/dashboard/menu-types/trash">
                                <Trash2 class="mr-2 h-4 w-4" />
                                Trash
                            </Link>
                        </Button>
                    </ButtonGroup>
                    <Button variant="outline" @click="handleExport">
                        <Download class="mr-2 h-4 w-4" />
                        Export
                    </Button>
                    <Button variant="outline" as-child>
                        <Link href="/dashboard/menu-types/import">
                            <Upload class="mr-2 h-4 w-4" />
                            Import
                        </Link>
                    </Button>
                    <Button @click="handleCreate">
                        <Plus class="mr-2 h-4 w-4" />
                        Add Menu Type
                    </Button>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid gap-4 md:grid-cols-4">
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
                <StatsCard
                    title="In Trash"
                    :value="props.stats.trashed ?? 0"
                    :icon="Trash2"
                    variant="destructive"
                />
            </div>

            <!-- Main Content -->
            <div class="flex flex-col gap-4">
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
                    <!-- Clear Filters Button -->
                    <Button
                        v-if="hasActiveFilters"
                        variant="ghost"
                        size="sm"
                        @click="handleClearFilters"
                        class="text-muted-foreground hover:text-foreground"
                    >
                        <X class="mr-1 h-4 w-4" />
                        Clear Filters
                    </Button>
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
