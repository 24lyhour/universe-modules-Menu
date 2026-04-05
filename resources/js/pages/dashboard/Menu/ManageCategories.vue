<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { VueDraggable } from 'vue-draggable-plus';
import AppLayout from '@/layouts/AppLayout.vue';
import { StatsCard } from '@/components/shared';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Switch } from '@/components/ui/switch';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    ArrowLeft,
    Plus,
    Layers,
    CheckCircle,
    XCircle,
    Search,
    Eye,
    Pencil,
    Trash2,
    Package,
    GripVertical,
    MoreHorizontal,
    Save,
    ChevronDown,
    ChevronRight,
    FolderPlus,
} from 'lucide-vue-next';
import { toast } from 'vue-sonner';
import type { BreadcrumbItem } from '@/types';
import type { Menu, CategoryFilters, CategoryStats } from '@menu/types';

interface CategoryProduct {
    id: number;
    name: string;
    sku: string | null;
    price: number;
    sale_price: number | null;
    status: string;
    image_url: string | null;
    pivot: {
        price_override: number | null;
        sort_order: number;
        is_available: boolean;
    };
}

interface CategoryWithProducts {
    id: number;
    uuid: string;
    name: string;
    description: string | null;
    image_url: string | null;
    sort_order: number;
    status: boolean;
    products_count: number;
    products: CategoryProduct[];
}

interface ManageCategoriesProps {
    menu: Menu;
    categories: CategoryWithProducts[];
    filters: CategoryFilters;
    stats: CategoryStats;
}

const props = defineProps<ManageCategoriesProps>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Menus', href: '/dashboard/menus' },
    { title: props.menu.name, href: `/dashboard/menus/${props.menu.uuid}` },
    { title: 'Manage Categories', href: `/dashboard/menus/${props.menu.uuid}/categories/manage` },
];

const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || 'all');
const isReordering = ref(false);
const isSaving = ref(false);

// Track which categories are expanded (using object for reactivity)
const expandedCategories = ref<Record<number, boolean>>({});

// Local copy of categories for drag-drop
const localCategories = ref<CategoryWithProducts[]>([...props.categories]);

// Watch for prop changes (deep: true to catch nested pivot updates)
watch(() => props.categories, (newData) => {
    localCategories.value = JSON.parse(JSON.stringify(newData));
}, { deep: true });

const isExpanded = (categoryId: number): boolean => {
    return expandedCategories.value[categoryId] ?? false;
};

const setExpanded = (categoryId: number, value: boolean) => {
    expandedCategories.value[categoryId] = value;
};

// Track product reordering state per category
const productReordering = ref<Record<number, boolean>>({});
const productSaving = ref<Record<number, boolean>>({});

const handleProductDragEnd = (categoryId: number) => {
    productReordering.value[categoryId] = true;
};

const saveProductOrder = (category: CategoryWithProducts) => {
    productSaving.value[category.id] = true;
    const reorderedProducts = category.products.map((product, index) => ({
        id: product.id,
        sort_order: index + 1,
    }));

    router.post(`/dashboard/categories/${category.id}/products/reorder`, {
        products: reorderedProducts,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            productReordering.value[category.id] = false;
            toast.success('Products reordered successfully');
        },
        onFinish: () => {
            productSaving.value[category.id] = false;
        },
    });
};

const cancelProductReorder = (category: CategoryWithProducts) => {
    // Reset products to original order
    const originalCategory = props.categories.find(c => c.id === category.id);
    if (originalCategory) {
        category.products = [...originalCategory.products];
    }
    productReordering.value[category.id] = false;
};

const handleSearch = () => {
    router.get(`/dashboard/menus/${props.menu.uuid}/categories/manage`, {
        search: search.value || undefined,
        status: statusFilter.value !== 'all' ? statusFilter.value : undefined,
    }, { preserveState: true });
};

watch(statusFilter, () => {
    router.get(`/dashboard/menus/${props.menu.uuid}/categories/manage`, {
        search: search.value || undefined,
        status: statusFilter.value !== 'all' ? statusFilter.value : undefined,
    }, { preserveState: true });
});

const handleCreate = () => {
    router.visit(`/dashboard/categories/create?menu_id=${props.menu.uuid}`);
};

const handleAssignExisting = () => {
    router.visit(`/dashboard/menus/${props.menu.uuid}/categories/assign`);
};

const handleStatusToggle = (category: CategoryWithProducts, newStatus: boolean) => {
    router.put(`/dashboard/categories/${category.id}/toggle-status`, {
        status: newStatus,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const handleDragEnd = () => {
    isReordering.value = true;
};

const saveOrder = () => {
    isSaving.value = true;
    const reorderedCategories = localCategories.value.map((cat, index) => ({
        id: cat.id,
        sort_order: index + 1,
    }));

    router.post(`/dashboard/menus/${props.menu.uuid}/categories/reorder`, {
        categories: reorderedCategories,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            isReordering.value = false;
            toast.success('Categories reordered successfully');
        },
        onFinish: () => {
            isSaving.value = false;
        },
    });
};

const cancelReorder = () => {
    localCategories.value = [...props.categories];
    isReordering.value = false;
};

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(value);
};

const handleProductAvailabilityToggle = (category: CategoryWithProducts, product: CategoryProduct, newValue: boolean) => {
    router.put(`/dashboard/categories/${category.id}/products/${product.id}/toggle-availability`, {
        is_available: newValue,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const getStatusVariant = (status: string) => {
    switch (status) {
        case 'active':
            return 'default';
        case 'inactive':
            return 'secondary';
        default:
            return 'outline';
    }
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`${menu.name} - Manage Categories`" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Button variant="outline" size="icon" as-child>
                        <Link :href="`/dashboard/menus`">
                            <ArrowLeft class="h-4 w-4" />
                        </Link>
                    </Button>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">{{ menu.name }} - Manage Categories</h1>
                        <p class="text-muted-foreground">Manage categories and products for this menu</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <div v-if="isReordering" class="flex items-center gap-2">
                        <Button variant="outline" @click="cancelReorder" :disabled="isSaving">
                            Cancel
                        </Button>
                        <Button @click="saveOrder" :disabled="isSaving">
                            <Save class="mr-2 h-4 w-4" />
                            {{ isSaving ? 'Saving...' : 'Save Order' }}
                        </Button>
                    </div>
                    <template v-else>
                        <Button variant="outline" @click="handleAssignExisting">
                            <FolderPlus class="mr-2 h-4 w-4" />
                            Add Existing
                        </Button>
                        <Button @click="handleCreate">
                            <Plus class="mr-2 h-4 w-4" />
                            Add Category
                        </Button>
                    </template>
                </div>
            </div>

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
            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <div>
                            <CardTitle>Categories & Products</CardTitle>
                            <CardDescription>Click on a category to view its products. Drag to reorder categories.</CardDescription>
                        </div>
                        <!-- Filters -->
                        <div class="flex items-center gap-4">
                            <div class="relative w-64">
                                <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                                <Input
                                    v-model="search"
                                    placeholder="Search categories..."
                                    class="pl-9"
                                    @keyup.enter="handleSearch"
                                />
                            </div>
                            <Select v-model="statusFilter">
                                <SelectTrigger class="w-[130px]">
                                    <SelectValue placeholder="Status" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">All Status</SelectItem>
                                    <SelectItem value="1">Active</SelectItem>
                                    <SelectItem value="0">Inactive</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                </CardHeader>
                <CardContent>
                    <!-- Empty State -->
                    <div v-if="localCategories.length === 0" class="flex flex-col items-center justify-center py-12 text-center">
                        <Layers class="h-12 w-12 text-muted-foreground mb-4" />
                        <h3 class="text-lg font-medium">No categories yet</h3>
                        <p class="text-sm text-muted-foreground mb-4">
                            This menu doesn't have any categories.
                        </p>
                        <Button @click="handleCreate">
                            <Plus class="mr-2 h-4 w-4" />
                            Add Category
                        </Button>
                    </div>

                    <!-- Draggable List with Collapsible Products -->
                    <VueDraggable
                        v-else
                        v-model="localCategories"
                        :animation="200"
                        handle=".drag-handle"
                        ghost-class="opacity-50"
                        @end="handleDragEnd"
                        class="space-y-3"
                    >
                        <Collapsible
                            v-for="category in localCategories"
                            :key="category.id"
                            :open="isExpanded(category.id)"
                            @update:open="(value: boolean) => setExpanded(category.id, value)"
                            class="rounded-lg border bg-card"
                        >
                            <!-- Category Header -->
                            <div class="flex items-center gap-4 p-4">
                                <!-- Drag Handle -->
                                <div class="drag-handle cursor-grab active:cursor-grabbing">
                                    <GripVertical class="h-5 w-5 text-muted-foreground" />
                                </div>

                                <!-- Expand/Collapse Button -->
                                <CollapsibleTrigger as-child>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="h-8 w-8"
                                    >
                                        <ChevronDown
                                            v-if="isExpanded(category.id)"
                                            class="h-4 w-4"
                                        />
                                        <ChevronRight v-else class="h-4 w-4" />
                                    </Button>
                                </CollapsibleTrigger>

                                <!-- Category Image -->
                                <div class="h-12 w-12 rounded-lg bg-muted overflow-hidden shrink-0">
                                    <img
                                        v-if="category.image_url"
                                        :src="category.image_url"
                                        :alt="category.name"
                                        class="h-full w-full object-cover"
                                    />
                                    <div v-else class="h-full w-full flex items-center justify-center">
                                        <Layers class="h-6 w-6 text-muted-foreground" />
                                    </div>
                                </div>

                                <!-- Category Info -->
                                <div class="flex-1 min-w-0 cursor-pointer" @click="setExpanded(category.id, !isExpanded(category.id))">
                                    <div class="flex items-center gap-2">
                                        <h3 class="font-medium truncate">{{ category.name }}</h3>
                                        <Badge variant="secondary" class="text-xs">
                                            {{ category.products_count ?? 0 }} products
                                        </Badge>
                                    </div>
                                    <p v-if="category.description" class="text-sm text-muted-foreground truncate">
                                        {{ category.description }}
                                    </p>
                                </div>

                                <!-- Status Toggle -->
                                <div class="flex items-center gap-2" @click.stop>
                                    <Switch
                                        :model-value="category.status"
                                        @update:model-value="handleStatusToggle(category, $event)"
                                    />
                                    <span class="text-sm text-muted-foreground w-16">
                                        {{ category.status ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>

                                <!-- Actions Menu -->
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button variant="ghost" size="icon">
                                            <MoreHorizontal class="h-4 w-4" />
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end">
                                        <DropdownMenuItem @click="router.visit(`/dashboard/categories/${category.id}/products/manage?return_to=menu&menu_id=${menu.uuid}`)">
                                            <Package class="mr-2 h-4 w-4" />
                                            Manage Products
                                        </DropdownMenuItem>
                                        <DropdownMenuItem @click="router.visit(`/dashboard/categories/${category.id}`)">
                                            <Eye class="mr-2 h-4 w-4" />
                                            View
                                        </DropdownMenuItem>
                                        <DropdownMenuItem @click="router.visit(`/dashboard/categories/${category.id}/edit`)">
                                            <Pencil class="mr-2 h-4 w-4" />
                                            Edit
                                        </DropdownMenuItem>
                                        <DropdownMenuSeparator />
                                        <DropdownMenuItem
                                            class="text-destructive"
                                            @click="router.visit(`/dashboard/categories/${category.id}/delete`)"
                                        >
                                            <Trash2 class="mr-2 h-4 w-4" />
                                            Delete
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </div>

                            <!-- Products List (Collapsible) -->
                            <CollapsibleContent>
                                <div class="border-t px-4 py-3 bg-muted/30">
                                    <!-- Products Header with Save/Cancel -->
                                    <div v-if="category.products.length > 0" class="flex items-center justify-between mb-3">
                                        <span class="text-sm font-medium text-muted-foreground">
                                            {{ category.products.length }} product{{ category.products.length !== 1 ? 's' : '' }}
                                        </span>
                                        <div v-if="productReordering[category.id]" class="flex items-center gap-2">
                                            <Button
                                                variant="outline"
                                                size="sm"
                                                @click="cancelProductReorder(category)"
                                                :disabled="productSaving[category.id]"
                                            >
                                                Cancel
                                            </Button>
                                            <Button
                                                size="sm"
                                                @click="saveProductOrder(category)"
                                                :disabled="productSaving[category.id]"
                                            >
                                                <Save class="mr-1 h-3 w-3" />
                                                {{ productSaving[category.id] ? 'Saving...' : 'Save' }}
                                            </Button>
                                        </div>
                                        <Button
                                            v-else
                                            variant="outline"
                                            size="sm"
                                            @click="router.visit(`/dashboard/categories/${category.id}/products/manage?return_to=menu&menu_id=${menu.uuid}`)"
                                        >
                                            <Plus class="mr-1 h-3 w-3" />
                                            Manage
                                        </Button>
                                    </div>

                                    <div v-if="category.products.length === 0" class="text-center py-4 text-muted-foreground text-sm">
                                        No products in this category.
                                        <Button
                                            variant="link"
                                            class="px-1"
                                            @click="router.visit(`/dashboard/categories/${category.id}/products/manage?return_to=menu&menu_id=${menu.uuid}`)"
                                        >
                                            Add products
                                        </Button>
                                    </div>

                                    <VueDraggable
                                        v-else
                                        v-model="category.products"
                                        :animation="200"
                                        handle=".product-drag-handle"
                                        ghost-class="opacity-50"
                                        @end="handleProductDragEnd(category.id)"
                                        class="space-y-2"
                                    >
                                        <div
                                            v-for="product in category.products"
                                            :key="product.id"
                                            class="flex items-center gap-3 p-3 rounded-lg bg-background border"
                                        >
                                            <!-- Drag Handle -->
                                            <div class="product-drag-handle cursor-grab active:cursor-grabbing">
                                                <GripVertical class="h-4 w-4 text-muted-foreground" />
                                            </div>

                                            <!-- Product Image -->
                                            <div class="h-10 w-10 rounded-lg bg-muted overflow-hidden shrink-0">
                                                <img
                                                    v-if="product.image_url"
                                                    :src="product.image_url"
                                                    :alt="product.name"
                                                    class="h-full w-full object-cover"
                                                />
                                                <div v-else class="h-full w-full flex items-center justify-center">
                                                    <Package class="h-5 w-5 text-muted-foreground" />
                                                </div>
                                            </div>

                                            <!-- Product Info -->
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center gap-2">
                                                    <span class="font-medium text-sm truncate">{{ product.name }}</span>
                                                    <Badge :variant="getStatusVariant(product.status)" class="text-xs">
                                                        {{ product.status }}
                                                    </Badge>
                                                </div>
                                                <div class="flex items-center gap-3 text-xs text-muted-foreground mt-0.5">
                                                    <code v-if="product.sku" class="rounded bg-muted px-1.5 py-0.5 font-mono">
                                                        {{ product.sku }}
                                                    </code>
                                                    <span v-if="product.sale_price" class="text-green-600">
                                                        {{ formatCurrency(product.sale_price) }}
                                                        <span class="line-through text-muted-foreground ml-1">{{ formatCurrency(product.price) }}</span>
                                                    </span>
                                                    <span v-else>{{ formatCurrency(product.price) }}</span>
                                                </div>
                                            </div>

                                            <!-- Override Price -->
                                            <div v-if="product.pivot.price_override" class="text-right text-sm">
                                                <span class="font-medium text-blue-600">
                                                    {{ formatCurrency(product.pivot.price_override) }}
                                                </span>
                                            </div>

                                            <!-- Available Toggle -->
                                            <div class="flex items-center gap-2" @click.stop>
                                                <Switch
                                                    :model-value="product.pivot.is_available"
                                                    @update:model-value="handleProductAvailabilityToggle(category, product, $event)"
                                                />
                                                <span class="text-sm text-muted-foreground w-16">
                                                    {{ product.pivot.is_available ? 'Active' : 'Inactive' }}
                                                </span>
                                            </div>
                                        </div>
                                    </VueDraggable>
                                </div>
                            </CollapsibleContent>
                        </Collapsible>
                    </VueDraggable>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
