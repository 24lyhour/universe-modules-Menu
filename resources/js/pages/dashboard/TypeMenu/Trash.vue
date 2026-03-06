<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import {
    RotateCcw,
    Trash2,
    ListOrdered,
    Database,
    AlertTriangle,
} from 'lucide-vue-next';
import { toast } from 'vue-sonner';

import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    TableReusable,
    ModalConfirm,
    StatsCard,
    ButtonGroup,
    type TableColumn,
    type TableAction,
} from '@/components/shared';
import { type BreadcrumbItem } from '@/types';
import type { MenuType, MenuTypeStats, PaginatedResponse, MenuTypeFilters } from '@menu/types';

interface MenuTypeTrashProps {
    menuTypeItems: PaginatedResponse<MenuType>;
    filters: MenuTypeFilters;
    stats: MenuTypeStats;
}

const props = defineProps<MenuTypeTrashProps>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Menus', href: '/dashboard/menus' },
    { title: 'Menu Types', href: '/dashboard/menu-types' },
    { title: 'Trash', href: '/dashboard/menu-types/trash' },
];

// Search
const searchQuery = ref(props.filters.search || '');

// Selection
const selectedUuids = ref<(string | number)[]>([]);

// Modal states
const isDeleteModalOpen = ref(false);
const isDeleting = ref(false);
const selectedMenuType = ref<MenuType | null>(null);
const isEmptyTrashModalOpen = ref(false);
const isEmptyingTrash = ref(false);

// Pagination
const pagination = computed(() => ({
    current_page: props.menuTypeItems.meta.current_page,
    last_page: props.menuTypeItems.meta.last_page,
    per_page: props.menuTypeItems.meta.per_page,
    total: props.menuTypeItems.meta.total,
}));

// Table columns
const columns: TableColumn<MenuType>[] = [
    { key: 'name', label: 'Name', width: '30%' },
    { key: 'outlet_name', label: 'Outlet' },
    { key: 'menus_count', label: 'Menus', align: 'center' },
    { key: 'status', label: 'Status' },
    { key: 'deleted_at', label: 'Deleted At' },
];

// Table actions
const tableActions: TableAction<MenuType>[] = [
    {
        label: 'Restore',
        icon: RotateCcw,
        onClick: (item) => handleRestore(item),
    },
    {
        label: 'Delete Permanently',
        icon: Trash2,
        onClick: (item) => openDeleteModal(item),
        variant: 'destructive',
        separator: true,
    },
];

// Handlers
const openDeleteModal = (menuType: MenuType) => {
    selectedMenuType.value = menuType;
    isDeleteModalOpen.value = true;
};

const handleRestore = (menuType: MenuType) => {
    router.put(`/dashboard/menu-types/${menuType.uuid}/restore`, {}, {
        onSuccess: () => {
            toast.success('Menu type restored successfully');
        },
    });
};

const handleForceDelete = () => {
    if (!selectedMenuType.value) return;
    isDeleting.value = true;
    router.delete(`/dashboard/menu-types/${selectedMenuType.value.uuid}/force-delete`, {
        onSuccess: () => {
            isDeleteModalOpen.value = false;
            selectedMenuType.value = null;
            toast.success('Menu type permanently deleted');
        },
        onFinish: () => {
            isDeleting.value = false;
        },
    });
};

const openBulkRestoreDialog = () => {
    router.put('/dashboard/menu-types/trash/bulk-restore', {
        uuids: selectedUuids.value,
    }, {
        onSuccess: () => {
            selectedUuids.value = [];
            toast.success('Selected menu types restored successfully');
        },
    });
};

const openBulkDeleteDialog = () => {
    router.delete('/dashboard/menu-types/trash/bulk-force-delete', {
        data: { uuids: selectedUuids.value },
        onSuccess: () => {
            selectedUuids.value = [];
            toast.success('Selected menu types permanently deleted');
        },
    });
};

const handleEmptyTrash = () => {
    isEmptyingTrash.value = true;
    router.delete('/dashboard/menu-types/trash/empty', {
        onSuccess: () => {
            isEmptyTrashModalOpen.value = false;
            toast.success('Trash emptied successfully');
        },
        onFinish: () => {
            isEmptyingTrash.value = false;
        },
    });
};

const handlePageChange = (page: number) => {
    router.get('/dashboard/menu-types/trash', {
        page,
        per_page: pagination.value.per_page,
        search: searchQuery.value,
    }, { preserveState: true, preserveScroll: true });
};

const handlePerPageChange = (perPage: number) => {
    router.get('/dashboard/menu-types/trash', {
        page: 1,
        per_page: perPage,
        search: searchQuery.value,
    }, { preserveState: true, preserveScroll: true });
};

const handleSearch = (search: string) => {
    searchQuery.value = search;
    router.get('/dashboard/menu-types/trash', {
        search,
    }, { preserveState: true, preserveScroll: true });
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Trashed Menu Types" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Trashed Menu Types</h1>
                    <p class="text-muted-foreground">Manage deleted menu types</p>
                </div>
                <div class="flex items-center gap-2">
                    <ButtonGroup>
                        <Button variant="outline" as-child>
                            <Link href="/dashboard/menu-types">
                                <Database class="mr-2 h-4 w-4" />
                                All
                            </Link>
                        </Button>
                        <Button variant="default">
                            <Trash2 class="mr-2 h-4 w-4" />
                            Trash
                        </Button>
                    </ButtonGroup>
                    <Button
                        v-if="props.menuTypeItems.data.length > 0"
                        variant="destructive"
                        @click="isEmptyTrashModalOpen = true"
                    >
                        <Trash2 class="mr-2 h-4 w-4" />
                        Empty Trash
                    </Button>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid gap-4 md:grid-cols-2">
                <StatsCard
                    title="In Trash"
                    :value="stats.trashed ?? 0"
                    :icon="Trash2"
                    variant="destructive"
                />
                <StatsCard
                    title="Total Active"
                    :value="stats.total"
                    :icon="ListOrdered"
                />
            </div>

            <!-- Empty State -->
            <div v-if="props.menuTypeItems.data.length === 0" class="flex flex-col items-center justify-center rounded-lg border border-dashed p-12 text-center">
                <Trash2 class="h-12 w-12 text-muted-foreground/50 mb-4" />
                <h3 class="text-lg font-semibold">Trash is empty</h3>
                <p class="text-muted-foreground mt-1 mb-4 max-w-md">
                    Deleted menu types will appear here.
                </p>
                <Button variant="outline" as-child>
                    <Link href="/dashboard/menu-types">
                        <Database class="mr-2 h-4 w-4" />
                        Back to Menu Types
                    </Link>
                </Button>
            </div>

            <!-- Table -->
            <TableReusable
                v-else
                v-model:selected="selectedUuids"
                :data="props.menuTypeItems.data"
                :columns="columns"
                :actions="tableActions"
                :pagination="pagination"
                :searchable="true"
                :selectable="true"
                select-key="uuid"
                search-placeholder="Search trashed menu types..."
                @page-change="handlePageChange"
                @per-page-change="handlePerPageChange"
                @search="handleSearch"
            >
                <!-- Bulk Actions -->
                <template #bulk-actions>
                    <Button variant="outline" size="sm" @click="openBulkRestoreDialog">
                        <RotateCcw class="mr-2 h-4 w-4" />
                        Restore Selected
                    </Button>
                    <Button variant="destructive" size="sm" @click="openBulkDeleteDialog">
                        <Trash2 class="mr-2 h-4 w-4" />
                        Delete Permanently
                    </Button>
                </template>

                <!-- Custom cell for name -->
                <template #cell-name="{ item }">
                    <div class="flex flex-col gap-1">
                        <span class="font-medium">{{ item.name }}</span>
                        <span v-if="item.description" class="max-w-[200px] truncate text-xs text-muted-foreground">
                            {{ item.description }}
                        </span>
                    </div>
                </template>

                <!-- Custom cell for outlet -->
                <template #cell-outlet_name="{ item }">
                    <span class="text-sm">{{ item.outlet_name || '-' }}</span>
                </template>

                <!-- Custom cell for menus count -->
                <template #cell-menus_count="{ item }">
                    <Badge variant="secondary" class="tabular-nums">
                        {{ item.menus_count || 0 }}
                    </Badge>
                </template>

                <!-- Custom cell for status -->
                <template #cell-status="{ item }">
                    <Badge :variant="item.status ? 'default' : 'secondary'">
                        {{ item.status ? 'Active' : 'Inactive' }}
                    </Badge>
                </template>

                <!-- Custom cell for deleted date -->
                <template #cell-deleted_at="{ item }">
                    <span class="text-sm text-muted-foreground">
                        {{ item.deleted_at ? formatDate(item.deleted_at) : '-' }}
                    </span>
                </template>
            </TableReusable>
        </div>

        <!-- Force Delete Confirmation Modal -->
        <ModalConfirm
            v-model:open="isDeleteModalOpen"
            title="Delete Permanently"
            :description="`Are you sure you want to permanently delete '${selectedMenuType?.name}'? This action cannot be undone.`"
            confirm-text="Delete Permanently"
            variant="danger"
            :loading="isDeleting"
            @confirm="handleForceDelete"
        >
            <template #default>
                <div class="flex items-start gap-3 rounded-lg border border-red-500/50 bg-red-500/10 p-3 mt-4">
                    <AlertTriangle class="mt-0.5 h-5 w-5 shrink-0 text-red-500" />
                    <div class="text-sm">
                        <p class="font-medium text-red-700 dark:text-red-400">
                            This action is irreversible
                        </p>
                        <p class="text-muted-foreground mt-1">
                            The menu type will be permanently removed.
                        </p>
                    </div>
                </div>
            </template>
        </ModalConfirm>

        <!-- Empty Trash Confirmation Modal -->
        <ModalConfirm
            v-model:open="isEmptyTrashModalOpen"
            title="Empty Trash"
            description="Are you sure you want to permanently delete all trashed menu types? This action cannot be undone."
            confirm-text="Empty Trash"
            variant="danger"
            :loading="isEmptyingTrash"
            @confirm="handleEmptyTrash"
        >
            <template #default>
                <div class="flex items-start gap-3 rounded-lg border border-red-500/50 bg-red-500/10 p-3 mt-4">
                    <AlertTriangle class="mt-0.5 h-5 w-5 shrink-0 text-red-500" />
                    <div class="text-sm">
                        <p class="font-medium text-red-700 dark:text-red-400">
                            This will delete {{ stats.trashed ?? 0 }} menu type(s)
                        </p>
                        <p class="text-muted-foreground mt-1">
                            All trashed menu types will be permanently removed.
                        </p>
                    </div>
                </div>
            </template>
        </ModalConfirm>
    </AppLayout>
</template>
