<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { VueDraggable } from 'vue-draggable-plus';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { ArrowLeft, Pencil, Package, Layers, CheckCircle, XCircle, GripVertical, Save } from 'lucide-vue-next';
import { toast } from 'vue-sonner';
import type { BreadcrumbItem } from '@/types';
import type { CategoryShowProps, CategoryProduct } from '@menu/types';

const props = defineProps<CategoryShowProps>();

// Drag-drop state
const isReordering = ref(false);
const isSaving = ref(false);
const localProducts = ref<CategoryProduct[]>([...props.products]);

// Watch for prop changes
watch(() => props.products, (newProducts) => {
    localProducts.value = [...newProducts];
});

const handleDragEnd = () => {
    isReordering.value = true;
};

const saveOrder = () => {
    isSaving.value = true;
    const reorderedProducts = localProducts.value.map((product, index) => ({
        id: product.id,
        sort_order: index + 1,
    }));

    router.post(`/dashboard/categories/${props.category.id}/products/reorder`, {
        products: reorderedProducts,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            isReordering.value = false;
            toast.success('Products reordered successfully');
        },
        onFinish: () => {
            isSaving.value = false;
        },
    });
};

const cancelReorder = () => {
    localProducts.value = [...props.products];
    isReordering.value = false;
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Categories', href: '/dashboard/categories' },
    { title: props.category.name, href: `/dashboard/categories/${props.category.id}` },
];

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(value);
};

const getStatusVariant = (status: string) => {
    switch (status) {
        case 'active':
            return 'default';
        case 'inactive':
            return 'secondary';
        case 'draft':
            return 'outline';
        default:
            return 'outline';
    }
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="category.name" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Button variant="outline" size="icon" as-child>
                        <Link href="/dashboard/categories">
                            <ArrowLeft class="h-4 w-4" />
                        </Link>
                    </Button>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">{{ category.name }}</h1>
                        <p class="text-muted-foreground">Category details</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Button variant="outline" as-child>
                        <Link :href="`/dashboard/categories/${category.id}/products/manage`">
                            <Package class="mr-2 h-4 w-4" />
                            Manage Products
                        </Link>
                    </Button>
                    <Button as-child>
                        <Link :href="`/dashboard/categories/${category.id}/edit`">
                            <Pencil class="mr-2 h-4 w-4" />
                            Edit Category
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- Content -->
            <div class="grid gap-6 md:grid-cols-2">
                <!-- Details Card -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Layers class="h-5 w-5" />
                            Category Information
                        </CardTitle>
                        <CardDescription>Basic details about this category</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">Name</p>
                                <p class="text-sm">{{ category.name }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">Status</p>
                                <Badge :variant="category.status ? 'default' : 'secondary'">
                                    {{ category.status ? 'Active' : 'Inactive' }}
                                </Badge>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">Sort Order</p>
                                <p class="text-sm">{{ category.sort_order }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">Products</p>
                                <p class="text-sm">{{ products.length }} products</p>
                            </div>
                        </div>
                        <div v-if="category.description">
                            <p class="text-sm font-medium text-muted-foreground">Description</p>
                            <p class="text-sm">{{ category.description }}</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Image & Meta Card -->
                <Card>
                    <CardHeader>
                        <CardTitle>Image & Metadata</CardTitle>
                        <CardDescription>Category image and timestamps</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div v-if="category.image_url" class="overflow-hidden rounded-lg">
                            <img
                                :src="category.image_url"
                                :alt="category.name"
                                class="h-48 w-full object-cover"
                            />
                        </div>
                        <div v-else class="flex h-48 items-center justify-center rounded-lg bg-muted">
                            <Layers class="h-12 w-12 text-muted-foreground" />
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">Created</p>
                                <p class="text-sm">{{ formatDate(category.created_at) }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">Updated</p>
                                <p class="text-sm">{{ formatDate(category.updated_at) }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Products List -->
            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <div>
                            <CardTitle class="flex items-center gap-2">
                                <Package class="h-5 w-5" />
                                Products in this Category
                            </CardTitle>
                            <CardDescription>
                                {{ localProducts.length }} product{{ localProducts.length !== 1 ? 's' : '' }} assigned to this category. Drag to reorder.
                            </CardDescription>
                        </div>
                        <div v-if="isReordering" class="flex items-center gap-2">
                            <Button variant="outline" @click="cancelReorder" :disabled="isSaving">
                                Cancel
                            </Button>
                            <Button @click="saveOrder" :disabled="isSaving">
                                <Save class="mr-2 h-4 w-4" />
                                {{ isSaving ? 'Saving...' : 'Save Order' }}
                            </Button>
                        </div>
                    </div>
                </CardHeader>
                <CardContent>
                    <div v-if="localProducts.length === 0" class="flex flex-col items-center justify-center py-12 text-center">
                        <Package class="h-12 w-12 text-muted-foreground mb-4" />
                        <h3 class="text-lg font-medium">No products yet</h3>
                        <p class="text-sm text-muted-foreground mb-4">
                            This category doesn't have any products assigned.
                        </p>
                        <Button as-child>
                            <Link :href="`/dashboard/categories/${category.id}/products/manage`">
                                <Package class="mr-2 h-4 w-4" />
                                Add Products
                            </Link>
                        </Button>
                    </div>

                    <VueDraggable
                        v-else
                        v-model="localProducts"
                        :animation="200"
                        handle=".drag-handle"
                        ghost-class="opacity-50"
                        @end="handleDragEnd"
                        class="space-y-2"
                    >
                        <div
                            v-for="product in localProducts"
                            :key="product.id"
                            class="flex items-center gap-4 rounded-lg border bg-card p-4 hover:bg-accent/50 transition-colors"
                        >
                            <!-- Drag Handle -->
                            <div class="drag-handle cursor-grab active:cursor-grabbing">
                                <GripVertical class="h-5 w-5 text-muted-foreground" />
                            </div>

                            <!-- Product Image -->
                            <div class="h-12 w-12 overflow-hidden rounded-lg bg-muted flex-shrink-0">
                                <img
                                    v-if="product.image_url"
                                    :src="product.image_url"
                                    :alt="product.name"
                                    class="h-full w-full object-cover"
                                />
                                <div v-else class="flex h-full w-full items-center justify-center">
                                    <Package class="h-6 w-6 text-muted-foreground" />
                                </div>
                            </div>

                            <!-- Product Info -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <h3 class="font-medium truncate">{{ product.name }}</h3>
                                    <Badge :variant="getStatusVariant(product.status)" class="text-xs">
                                        {{ product.status }}
                                    </Badge>
                                </div>
                                <div class="flex items-center gap-4 text-sm text-muted-foreground">
                                    <code v-if="product.sku" class="rounded bg-muted px-2 py-0.5 text-xs font-mono">
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
                            <div class="text-right">
                                <p class="text-xs text-muted-foreground">Override</p>
                                <span v-if="product.pivot.price_override" class="font-medium text-blue-600">
                                    {{ formatCurrency(product.pivot.price_override) }}
                                </span>
                                <span v-else class="text-muted-foreground">-</span>
                            </div>

                            <!-- Available Status -->
                            <div class="flex items-center gap-2">
                                <CheckCircle v-if="product.pivot.is_available" class="h-5 w-5 text-green-600" />
                                <XCircle v-else class="h-5 w-5 text-red-500" />
                                <span class="text-sm text-muted-foreground">
                                    {{ product.pivot.is_available ? 'Available' : 'Unavailable' }}
                                </span>
                            </div>
                        </div>
                    </VueDraggable>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
