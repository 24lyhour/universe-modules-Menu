<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
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
import { ArrowLeft, Plus, Layers, CheckCircle, XCircle, Search, Eye, Pencil, Trash2, Package, GripVertical } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import type { BreadcrumbItem } from '@/types';

interface Menu {
    id: number;
    name: string;
}

interface Category {
    id: number;
    name: string;
    description?: string;
    status: boolean;
    sort_order: number;
    products_count?: number;
}

interface Props {
    menu: Menu;
    categories: {
        data: Category[];
        meta: {
            current_page: number;
            last_page: number;
            per_page: number;
            total: number;
        };
    };
    filters: {
        search?: string;
        status?: string;
    };
    stats: {
        total: number;
        active: number;
        inactive: number;
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Menus', href: '/dashboard/menus' },
    { title: props.menu.name, href: `/dashboard/menus/${props.menu.id}` },
    { title: 'Categories', href: `/dashboard/menus/${props.menu.id}/categories` },
];

const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || 'all');

const columns: TableColumn<Category>[] = [
    {
        key: 'sort_order',
        label: '',
        render: () => '',
    },
    {
        key: 'name',
        label: 'Category Name',
        render: (category) => category.name,
    },
    {
        key: 'products_count',
        label: 'Products',
        render: (category) => (category.products_count ?? 0).toString(),
    },
    {
        key: 'status',
        label: 'Status',
        render: (category) => category.status ? 'Active' : 'Inactive',
    },
];

const actions: TableAction<Category>[] = [
    {
        label: 'Products',
        icon: Package,
        onClick: (category) => router.visit(`/dashboard/categories/${category.id}/products/manage`),
    },
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
];

const pagination = computed<PaginationData>(() => ({
    current_page: props.categories.meta.current_page,
    last_page: props.categories.meta.last_page,
    per_page: props.categories.meta.per_page,
    total: props.categories.meta.total,
}));

const handlePageChange = (page: number) => {
    router.get(`/dashboard/menus/${props.menu.id}/categories`, {
        page,
        per_page: pagination.value.per_page,
        search: search.value || undefined,
        status: statusFilter.value !== 'all' ? statusFilter.value : undefined,
    }, { preserveState: true });
};

const handlePerPageChange = (perPage: number) => {
    router.get(`/dashboard/menus/${props.menu.id}/categories`, {
        per_page: perPage,
        search: search.value || undefined,
        status: statusFilter.value !== 'all' ? statusFilter.value : undefined,
    }, { preserveState: true });
};

const handleSearch = () => {
    router.get(`/dashboard/menus/${props.menu.id}/categories`, {
        search: search.value || undefined,
        status: statusFilter.value !== 'all' ? statusFilter.value : undefined,
    }, { preserveState: true });
};

watch(statusFilter, () => {
    router.get(`/dashboard/menus/${props.menu.id}/categories`, {
        search: search.value || undefined,
        status: statusFilter.value !== 'all' ? statusFilter.value : undefined,
    }, { preserveState: true });
});

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
        <Head :title="`${menu.name} - Categories`" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center gap-4">
                <Button variant="ghost" size="icon" as-child>
                    <Link :href="`/dashboard/menus/${menu.id}`">
                        <ArrowLeft class="h-4 w-4" />
                    </Link>
                </Button>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">{{ menu.name }} - Categories</h1>
                    <p class="text-muted-foreground">Manage categories for this menu</p>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid gap-4 md:grid-cols-3">
                <StatsCard
                    title="Total Categories"
                    :value="stats.total"
                    :icon="Layers"
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
                        <h2 class="text-lg font-semibold">Categories</h2>
                        <p class="text-sm text-muted-foreground">Categories assigned to this menu</p>
                    </div>
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
                    :data="categories.data"
                    :columns="columns"
                    :actions="actions"
                    :pagination="pagination"
                    :searchable="false"
                    @page-change="handlePageChange"
                    @per-page-change="handlePerPageChange"
                >
                    <template #cell-sort_order>
                        <div class="flex items-center text-muted-foreground cursor-grab">
                            <GripVertical class="h-4 w-4" />
                        </div>
                    </template>

                    <template #cell-products_count="{ item }">
                        <Link
                            :href="`/dashboard/categories/${item.id}/products/manage`"
                            class="inline-block"
                        >
                            <Badge
                                :variant="(item.products_count ?? 0) > 0 ? 'default' : 'secondary'"
                                class="cursor-pointer"
                            >
                                <Package class="mr-1 h-3 w-3" />
                                {{ item.products_count ?? 0 }}
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
