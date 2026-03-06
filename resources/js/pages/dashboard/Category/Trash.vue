<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import {
    RotateCcw,
    Trash2,
    Layers,
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
import type { Category, CategoryStats, PaginatedResponse, CategoryFilters } from '@menu/types';

interface CategoryTrashProps {
    categoryItems: PaginatedResponse<Category>;
    filters: CategoryFilters;
    stats: CategoryStats;
}

const props = defineProps<CategoryTrashProps>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Menus', href: '/dashboard/menus' },
    { title: 'Categories', href: '/dashboard/categories' },
    { title: 'Trash', href: '/dashboard/categories/trash' },
];

// Search
const searchQuery = ref(props.filters.search || '');

// Selection
const selectedUuids = ref<(string | number)[]>([]);

// Modal states
const isDeleteModalOpen = ref(false);
const isDeleting = ref(false);
const selectedCategory = ref<Category | null>(null);
const isEmptyTrashModalOpen = ref(false);
const isEmptyingTrash = ref(false);

// Pagination
const pagination = computed(() => ({
    current_page: props.categoryItems.meta.current_page,
    last_page: props.categoryItems.meta.last_page,
    per_page: props.categoryItems.meta.per_page,
    total: props.categoryItems.meta.total,
}));

// Table columns
const columns: TableColumn<Category>[] = [
    { key: 'name', label: 'Name', width: '30%' },
    { key: 'products_count', label: 'Products', align: 'center' },
    { key: 'sort_order', label: 'Order', align: 'center' },
    { key: 'status', label: 'Status' },
    { key: 'deleted_at', label: 'Deleted At' },
];

// Table actions
const tableActions: TableAction<Category>[] = [
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
const openDeleteModal = (category: Category) => {
    selectedCategory.value = category;
    isDeleteModalOpen.value = true;
};

const handleRestore = (category: Category) => {
    router.put(`/dashboard/categories/${category.uuid}/restore`, {}, {
        onSuccess: () => {
            toast.success('Category restored successfully');
        },
    });
};

const handleForceDelete = () => {
    if (!selectedCategory.value) return;
    isDeleting.value = true;
    router.delete(`/dashboard/categories/${selectedCategory.value.uuid}/force-delete`, {
        onSuccess: () => {
            isDeleteModalOpen.value = false;
            selectedCategory.value = null;
            toast.success('Category permanently deleted');
        },
        onFinish: () => {
            isDeleting.value = false;
        },
    });
};

const openBulkRestoreDialog = () => {
    router.put('/dashboard/categories/trash/bulk-restore', {
        uuids: selectedUuids.value,
    }, {
        onSuccess: () => {
            selectedUuids.value = [];
            toast.success('Selected categories restored successfully');
        },
    });
};

const openBulkDeleteDialog = () => {
    router.delete('/dashboard/categories/trash/bulk-force-delete', {
        data: { uuids: selectedUuids.value },
        onSuccess: () => {
            selectedUuids.value = [];
            toast.success('Selected categories permanently deleted');
        },
    });
};

const handleEmptyTrash = () => {
    isEmptyingTrash.value = true;
    router.delete('/dashboard/categories/trash/empty', {
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
    router.get('/dashboard/categories/trash', {
        page,
        per_page: pagination.value.per_page,
        search: searchQuery.value,
    }, { preserveState: true, preserveScroll: true });
};

const handlePerPageChange = (perPage: number) => {
    router.get('/dashboard/categories/trash', {
        page: 1,
        per_page: perPage,
        search: searchQuery.value,
    }, { preserveState: true, preserveScroll: true });
};

const handleSearch = (search: string) => {
    searchQuery.value = search;
    router.get('/dashboard/categories/trash', {
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
        <Head title="Trashed Categories" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Trashed Categories</h1>
                    <p class="text-muted-foreground">Manage deleted categories</p>
                </div>
                <div class="flex items-center gap-2">
                    <ButtonGroup>
                        <Button variant="outline" as-child>
                            <Link href="/dashboard/categories">
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
                        v-if="props.categoryItems.data.length > 0"
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
                    :icon="Layers"
                />
            </div>

            <!-- Empty State -->
            <div v-if="props.categoryItems.data.length === 0" class="flex flex-col items-center justify-center rounded-lg border border-dashed p-12 text-center">
                <Trash2 class="h-12 w-12 text-muted-foreground/50 mb-4" />
                <h3 class="text-lg font-semibold">Trash is empty</h3>
                <p class="text-muted-foreground mt-1 mb-4 max-w-md">
                    Deleted categories will appear here.
                </p>
                <Button variant="outline" as-child>
                    <Link href="/dashboard/categories">
                        <Database class="mr-2 h-4 w-4" />
                        Back to Categories
                    </Link>
                </Button>
            </div>

            <!-- Table -->
            <TableReusable
                v-else
                v-model:selected="selectedUuids"
                :data="props.categoryItems.data"
                :columns="columns"
                :actions="tableActions"
                :pagination="pagination"
                :searchable="true"
                :selectable="true"
                select-key="uuid"
                search-placeholder="Search trashed categories..."
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

                <!-- Custom cell for products count -->
                <template #cell-products_count="{ item }">
                    <Badge variant="secondary" class="tabular-nums">
                        {{ item.products_count || 0 }}
                    </Badge>
                </template>

                <!-- Custom cell for sort order -->
                <template #cell-sort_order="{ item }">
                    <span class="text-muted-foreground">{{ item.sort_order }}</span>
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
            :description="`Are you sure you want to permanently delete '${selectedCategory?.name}'? This action cannot be undone.`"
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
                            The category and all its product associations will be permanently removed.
                        </p>
                    </div>
                </div>
            </template>
        </ModalConfirm>

        <!-- Empty Trash Confirmation Modal -->
        <ModalConfirm
            v-model:open="isEmptyTrashModalOpen"
            title="Empty Trash"
            description="Are you sure you want to permanently delete all trashed categories? This action cannot be undone."
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
                            This will delete {{ stats.trashed ?? 0 }} category(s)
                        </p>
                        <p class="text-muted-foreground mt-1">
                            All trashed categories and their product associations will be permanently removed.
                        </p>
                    </div>
                </div>
            </template>
        </ModalConfirm>
    </AppLayout>
</template>
