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
import { Plus, Layers, CheckCircle, XCircle, Search, Eye, Pencil, Trash2, Package, ExternalLink } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Link } from '@inertiajs/vue3';
import type { BreadcrumbItem } from '@/types';
import type { CategoryIndexProps, Category } from '@menu/types';

const props = defineProps<CategoryIndexProps>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Menus', href: '/dashboard/menus' },
    { title: 'Categories', href: '/dashboard/categories' },
];

const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || 'all');

const columns: TableColumn<Category>[] = [
    {
        key: 'name',
        label: 'Category Name',
        render: (category) => category.name,
    },
    {
        key: 'description',
        label: 'Description',
        render: (category) => category.description || '-',
    },
    {
        key: 'products_count',
        label: 'Products',
        render: (category) => (category.products_count ?? 0).toString(),
    },
    {
        key: 'sort_order',
        label: 'Sort Order',
        render: (category) => category.sort_order.toString(),
    },
    {
        key: 'status',
        label: 'Status',
        render: (category) => category.status ? 'Active' : 'Inactive',
    },
];

const actions: TableAction<Category>[] = [
    {
        label: 'View',
        icon: Eye,
        onClick: (category) => router.visit(`/dashboard/categories/${category.id}`),
    },
    {
        label: 'Edit',
        icon: Pencil,
        onClick: (category) => router.visit(`/dashboard/categories/${category.id}/edit`),
    },
    {
        label: 'Manage Products',
        icon: Package,
        onClick: (category) => router.visit(`/dashboard/categories/${category.id}/products/manage`),
    },
    {
        label: 'Delete',
        icon: Trash2,
        onClick: (category) => router.visit(`/dashboard/categories/${category.id}/delete`),
        variant: 'destructive',
        separator: true,
    },
];

const pagination = computed<PaginationData>(() => ({
    current_page: props.categories.meta.current_page,
    last_page: props.categories.meta.last_page,
    per_page: props.categories.meta.per_page,
    total: props.categories.meta.total,
}));

const handlePageChange = (page: number) => {
    router.get('/dashboard/categories', {
        page,
        per_page: pagination.value.per_page,
        search: search.value || undefined,
        status: statusFilter.value !== 'all' ? statusFilter.value : undefined,
    }, { preserveState: true });
};

const handlePerPageChange = (perPage: number) => {
    router.get('/dashboard/categories', {
        per_page: perPage,
        search: search.value || undefined,
        status: statusFilter.value !== 'all' ? statusFilter.value : undefined,
    }, { preserveState: true });
};

const handleSearch = () => {
    router.get('/dashboard/categories', {
        search: search.value || undefined,
        status: statusFilter.value !== 'all' ? statusFilter.value : undefined,
    }, { preserveState: true });
};

watch(statusFilter, () => {
    router.get('/dashboard/categories', {
        search: search.value || undefined,
        status: statusFilter.value !== 'all' ? statusFilter.value : undefined,
    }, { preserveState: true });
});

const handleCreate = () => {
    router.visit('/dashboard/categories/create');
};

const handleStatusToggle = (category: Category, newStatus: boolean) => {
    router.put(`/dashboard/categories/${category.id}/toggle-status`, {
        status: newStatus,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Categories" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Stats -->
            <div class="grid gap-4 md:grid-cols-3">
                <StatsCard
                    title="Total Categories"
                    :value="props.stats.total"
                    :icon="Layers"
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
                        <h2 class="text-lg font-semibold">Categories</h2>
                        <p class="text-sm text-muted-foreground">Manage your categories</p>
                    </div>
                    <Button @click="handleCreate">
                        <Plus class="mr-2 h-4 w-4" />
                        Add Category
                    </Button>
                </div>

                <!-- Filters -->
                <div class="flex items-center gap-4">
                    <div class="relative flex-1 max-w-sm">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                        <Input
                            v-model="search"
                            placeholder="Search categories..."
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
                    :data="props.categories.data"
                    :columns="columns"
                    :actions="actions"
                    :pagination="pagination"
                    :searchable="false"
                    @page-change="handlePageChange"
                    @per-page-change="handlePerPageChange"
                >
                    <template #cell-products_count="{ item }">
                        <Link
                            :href="`/dashboard/categories/${item.id}/products/manage`"
                            class="inline-block"
                        >
                            <Badge
                                :variant="(item.products_count ?? 0) > 0 ? 'default' : 'secondary'"
                                class="gap-1.5 cursor-pointer hover:bg-primary/80 transition-colors"
                            >
                                <Package class="h-3 w-3" />
                                {{ item.products_count ?? 0 }}
                                <ExternalLink class="h-3 w-3 opacity-50" />
                            </Badge>
                        </Link>
                    </template>

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
