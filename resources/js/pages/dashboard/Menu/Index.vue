<script setup lang="ts">
import { computed, ref, watch, type VNode } from 'vue';
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
import { Badge } from '@/components/ui/badge';
import { Plus, UtensilsCrossed, CheckCircle, XCircle, Search, Eye, Pencil, Trash2, Layers, FolderTree, Package, ExternalLink, Clock, CalendarClock, Download, Upload, FileSpreadsheet, Database, X, Megaphone, BellOff } from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';
import type { MenuIndexProps, Menu } from '@menu/types';
import { toast } from '@/composables/useToast';

// Persistent layout for momentum-modal
defineOptions({
    layout: (h: (type: unknown, props: unknown, children: unknown) => VNode, page: VNode) =>
        h(AppLayout, {
            breadcrumbs: [
                { title: 'Dashboard', href: '/dashboard' },
                { title: 'Menus', href: '/dashboard/menus' },
            ] as BreadcrumbItem[]
        }, () => page),
});

const props = defineProps<MenuIndexProps>();

const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || 'all');
const selectedUuids = ref<(string | number)[]>([]);

// Check if any filters are active
const hasActiveFilters = computed(() => {
    return !!(
        search.value ||
        (statusFilter.value !== 'all' && statusFilter.value !== '')
    );
});

const columns: TableColumn<Menu>[] = [
    {
        key: 'name',
        label: 'Menu Name',
        render: (menu) => menu.name,
    },
    {
        key: 'outlet_name',
        label: 'Outlet',
        render: (menu) => menu.outlet_name || '-',
    },
    {
        key: 'menu_type_name',
        label: 'Menu Type',
        render: (menu) => menu.menu_type_name || '-',
    },
    {
        key: 'categories_count',
        label: 'Categories',
        render: (menu) => String(menu.categories_count),
    },
    {
        key: 'products_count',
        label: 'Products',
        render: (menu) => String(menu.products_count),
    },
    {
        key: 'schedule',
        label: 'Schedule',
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
        onClick: (menu) => router.visit(`/dashboard/menus/${menu.uuid}`),
    },
    {
        label: 'Edit',
        icon: Pencil,
        onClick: (menu) => router.visit(`/dashboard/menus/${menu.uuid}/edit`),
    },
    {
        label: 'Manage Categories',
        icon: Layers,
        onClick: (menu) => router.visit(`/dashboard/menus/${menu.uuid}/categories/manage`),
    },
    {
        label: 'Schedule',
        icon: Clock,
        onClick: (menu) => router.visit(`/dashboard/menus/${menu.uuid}/schedule`),
    },
    {
        label: 'Mute / Unmute',
        icon: Megaphone,
        onClick: (menu) => router.visit(`/dashboard/menus/${menu.uuid}/mute`),
    },
    {
        label: 'Delete',
        icon: Trash2,
        onClick: (menu) => router.visit(`/dashboard/menus/${menu.uuid}/delete`),
        variant: 'destructive',
        separator: true,
    },
];

const isCurrentlyMuted = (menu: Menu): boolean => {
    if (!menu.is_muted) return false;
    if (!menu.muted_until) return true;
    return new Date(menu.muted_until).getTime() > Date.now();
};

const pagination = computed<PaginationData>(() => ({
    current_page: props.menuItems.meta.current_page,
    last_page: props.menuItems.meta.last_page,
    per_page: props.menuItems.meta.per_page,
    total: props.menuItems.meta.total,
}));

const handlePageChange = (page: number) => {
    router.get('/dashboard/menus', {
        page,
        per_page: pagination.value.per_page,
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

const handleExport = () => {
    const params = new URLSearchParams();
    if (search.value) params.append('search', search.value);
    if (statusFilter.value && statusFilter.value !== 'all') params.append('status', statusFilter.value);
    window.location.href = `/dashboard/menus/export?${params.toString()}`;
};

const handleImport = () => {
    router.visit('/dashboard/menus/import');
};

const handleDownloadTemplate = () => {
    window.location.href = '/dashboard/menus/import/template';
};

const handleClearFilters = () => {
    search.value = '';
    statusFilter.value = 'all';
    router.get('/dashboard/menus', {}, { preserveState: true, preserveScroll: true });
};

const handleStatusToggle = (menu: Menu, newStatus: boolean) => {
    router.put(`/dashboard/menus/${menu.uuid}/toggle-status`, {
        status: newStatus,
    }, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            toast.success(`Menu ${newStatus ? 'activated' : 'deactivated'} successfully.`);
        },
    });
};

const openBulkDeleteDialog = () => {
    const params = new URLSearchParams();
    selectedUuids.value.forEach((uuid) => {
        params.append('uuids[]', String(uuid));
    });
    router.visit(`/dashboard/menus/bulk-delete?${params.toString()}`);
};
</script>

<template>
    <div>
        <Head title="Menus" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Menus</h1>
                    <p class="text-muted-foreground">Manage your menus</p>
                </div>
                <div class="flex items-center gap-2">
                    <ButtonGroup>
                        <Button variant="default">
                            <Database class="mr-2 h-4 w-4" />
                            All
                        </Button>
                        <Button variant="outline" as-child>
                            <Link href="/dashboard/menus/trash">
                                <Trash2 class="mr-2 h-4 w-4" />
                                Trash
                            </Link>
                        </Button>
                    </ButtonGroup>
                    <ButtonGroup>
                        <Button variant="outline" @click="handleExport">
                            <Download class="mr-2 h-4 w-4" />
                            Export
                        </Button>
                        <Button variant="outline" @click="handleImport">
                            <Upload class="mr-2 h-4 w-4" />
                            Import
                        </Button>
                        <Button variant="outline" @click="handleDownloadTemplate">
                            <FileSpreadsheet class="mr-2 h-4 w-4" />
                            Template
                        </Button>
                    </ButtonGroup>
                    <Button @click="handleCreate">
                        <Plus class="mr-2 h-4 w-4" />
                        Add Menu
                    </Button>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid gap-4 md:grid-cols-4">
                <StatsCard
                    title="Total Menus"
                    :value="props.stats.total"
                    :icon="UtensilsCrossed"
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
                    :data="props.menuItems.data"
                    :columns="columns"
                    :actions="actions"
                    :pagination="pagination"
                    :searchable="false"
                    :selectable="true"
                    select-key="uuid"
                    v-model:selected="selectedUuids"
                    @page-change="handlePageChange"
                    @per-page-change="handlePerPageChange"
                >
                    <template #bulk-actions>
                        <Button
                            v-if="selectedUuids.length > 0"
                            variant="destructive"
                            size="sm"
                            @click="openBulkDeleteDialog"
                        >
                            <Trash2 class="mr-2 h-4 w-4" />
                            Delete {{ selectedUuids.length }} Selected
                        </Button>
                    </template>
                    <template #cell-categories_count="{ item }">
                        <Badge
                            variant="secondary"
                            class="gap-1.5 cursor-pointer hover:bg-secondary/80 transition-colors"
                            @click.stop="router.visit(`/dashboard/menus/${item.uuid}/categories/manage`)"
                        >
                            <FolderTree class="h-3 w-3" />
                            {{ item.categories_count }}
                            <ExternalLink class="h-3 w-3 opacity-50" />
                        </Badge>
                    </template>
                    <template #cell-products_count="{ item }">
                        <Badge
                            variant="outline"
                            class="gap-1.5 cursor-pointer hover:bg-muted transition-colors"
                            @click.stop="router.visit(`/dashboard/menus/${item.uuid}/categories/manage`)"
                        >
                            <Package class="h-3 w-3" />
                            {{ item.products_count }}
                            <ExternalLink class="h-3 w-3 opacity-50" />
                        </Badge>
                    </template>
                    <template #cell-schedule="{ item }">
                        <Badge
                            v-if="item.schedule_status"
                            variant="default"
                            class="gap-1.5 cursor-pointer hover:bg-primary/80 transition-colors"
                            @click.stop="router.visit(`/dashboard/menus/${item.uuid}/schedule`)"
                        >
                            <CalendarClock class="h-3 w-3" />
                            {{ item.schedule_mode === 'always' ? 'Always' : item.schedule_start_time ? `${item.schedule_start_time} - ${item.schedule_end_time}` : 'Configured' }}
                        </Badge>
                        <Badge
                            v-else
                            variant="outline"
                            class="gap-1.5 cursor-pointer hover:bg-muted transition-colors text-muted-foreground"
                            @click.stop="router.visit(`/dashboard/menus/${item.uuid}/schedule`)"
                        >
                            <Clock class="h-3 w-3" />
                            Not Set
                        </Badge>
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
                            <Badge
                                v-if="isCurrentlyMuted(item)"
                                variant="secondary"
                                class="gap-1 cursor-pointer bg-amber-100 text-amber-800 hover:bg-amber-200 dark:bg-amber-950/50 dark:text-amber-300"
                                @click.stop="router.visit(`/dashboard/menus/${item.uuid}/mute`)"
                            >
                                <BellOff class="h-3 w-3" />
                                Muted
                            </Badge>
                            <Megaphone
                                v-else-if="item.status"
                                class="h-3.5 w-3.5 cursor-pointer text-muted-foreground/40 hover:text-muted-foreground"
                                @click.stop="router.visit(`/dashboard/menus/${item.uuid}/mute`)"
                            />
                        </div>
                    </template>
                </TableReusable>
            </div>
        </div>
    </div>
</template>
