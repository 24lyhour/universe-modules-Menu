<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { ArrowLeft, Pencil, UtensilsCrossed, Layers, Calendar, Clock, Package, ChevronRight } from 'lucide-vue-next';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import { ref } from 'vue';
import type { BreadcrumbItem } from '@/types';
import type { Menu } from '@menu/types';

interface CategoryProduct {
    id: number;
    name: string;
    sku: string | null;
    price: number;
    sale_price: number | null;
    status: string;
    image_url: string | null;
}

interface CategoryWithProducts {
    id: number;
    name: string;
    description: string | null;
    image_url: string | null;
    sort_order: number;
    status: boolean;
    products_count: number;
    products: CategoryProduct[];
}

interface Props {
    menu: Menu;
    categories: CategoryWithProducts[];
}

const props = defineProps<Props>();

const expandedCategories = ref<Record<number, boolean>>({});

const toggleCategory = (categoryId: number) => {
    expandedCategories.value[categoryId] = !expandedCategories.value[categoryId];
};

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(value);
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Menus', href: '/dashboard/menus' },
    { title: props.menu.name, href: `/dashboard/menus/${props.menu.id}` },
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

const formatTime = (time: string | null) => {
    if (!time) return '-';
    return time;
};

const formatDays = (days: string | null) => {
    if (!days) return '-';
    return days;
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="menu.name" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Button variant="outline" size="icon" as-child>
                        <Link href="/dashboard/menus">
                            <ArrowLeft class="h-4 w-4" />
                        </Link>
                    </Button>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">{{ menu.name }}</h1>
                        <p class="text-muted-foreground">Menu details</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Button variant="outline" as-child>
                        <Link :href="`/dashboard/menus/${menu.id}/categories/manage`">
                            <Layers class="mr-2 h-4 w-4" />
                            Manage Categories
                        </Link>
                    </Button>
                    <Button as-child>
                        <Link :href="`/dashboard/menus/${menu.id}/edit`">
                            <Pencil class="mr-2 h-4 w-4" />
                            Edit Menu
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
                            <UtensilsCrossed class="h-5 w-5" />
                            Menu Information
                        </CardTitle>
                        <CardDescription>Basic details about this menu</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">Name</p>
                                <p class="text-sm">{{ menu.name }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">Status</p>
                                <Badge :variant="menu.status ? 'default' : 'secondary'">
                                    {{ menu.status ? 'Active' : 'Inactive' }}
                                </Badge>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">Outlet</p>
                                <p class="text-sm">{{ menu.outlet_name || '-' }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">Menu Type</p>
                                <p class="text-sm">{{ menu.menu_type_name || '-' }}</p>
                            </div>
                        </div>
                        <div v-if="menu.description">
                            <p class="text-sm font-medium text-muted-foreground">Description</p>
                            <p class="text-sm">{{ menu.description }}</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Image & Meta Card -->
                <Card>
                    <CardHeader>
                        <CardTitle>Image & Metadata</CardTitle>
                        <CardDescription>Menu image and timestamps</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div v-if="menu.image_url" class="overflow-hidden rounded-lg">
                            <img
                                :src="menu.image_url"
                                :alt="menu.name"
                                class="h-48 w-full object-cover"
                            />
                        </div>
                        <div v-else class="flex h-48 items-center justify-center rounded-lg bg-muted">
                            <UtensilsCrossed class="h-12 w-12 text-muted-foreground" />
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">Created</p>
                                <p class="text-sm">{{ formatDate(menu.created_at) }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">Updated</p>
                                <p class="text-sm">{{ formatDate(menu.updated_at) }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Schedule Card -->
                <Card class="md:col-span-2" v-if="menu.schedule_mode">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Calendar class="h-5 w-5" />
                            Schedule
                        </CardTitle>
                        <CardDescription>Menu availability schedule</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">Schedule Mode</p>
                                <p class="text-sm capitalize">{{ menu.schedule_mode || '-' }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">Days</p>
                                <p class="text-sm">{{ formatDays(menu.schedule_days) }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">Time</p>
                                <p class="text-sm flex items-center gap-1">
                                    <Clock class="h-3 w-3" />
                                    {{ formatTime(menu.schedule_start_time) }} - {{ formatTime(menu.schedule_end_time) }}
                                </p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">Schedule Status</p>
                                <Badge :variant="menu.schedule_status ? 'default' : 'secondary'">
                                    {{ menu.schedule_status ? 'Active' : 'Inactive' }}
                                </Badge>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Categories & Products Card -->
                <Card class="md:col-span-2">
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <div>
                                <CardTitle class="flex items-center gap-2">
                                    <Layers class="h-5 w-5" />
                                    Categories & Products
                                </CardTitle>
                                <CardDescription>{{ categories.length }} categories in this menu</CardDescription>
                            </div>
                            <Button variant="outline" size="sm" as-child>
                                <Link :href="`/dashboard/menus/${menu.id}/categories/manage`">
                                    Manage
                                </Link>
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div v-if="categories.length === 0" class="text-center py-8 text-muted-foreground">
                            <Layers class="h-12 w-12 mx-auto mb-4 opacity-50" />
                            <p>No categories in this menu yet.</p>
                            <Button variant="link" as-child class="mt-2">
                                <Link :href="`/dashboard/menus/${menu.id}/categories/manage`">
                                    Add categories
                                </Link>
                            </Button>
                        </div>

                        <div v-else class="space-y-3">
                            <Collapsible
                                v-for="category in categories"
                                :key="category.id"
                                :open="expandedCategories[category.id]"
                                class="rounded-lg border"
                            >
                                <CollapsibleTrigger
                                    class="flex w-full items-center gap-3 p-4 hover:bg-muted/50 transition-colors"
                                    @click="toggleCategory(category.id)"
                                >
                                    <ChevronRight
                                        :class="[
                                            'h-4 w-4 transition-transform',
                                            expandedCategories[category.id] && 'rotate-90'
                                        ]"
                                    />
                                    <div class="h-10 w-10 rounded-lg bg-muted overflow-hidden shrink-0">
                                        <img
                                            v-if="category.image_url"
                                            :src="category.image_url"
                                            :alt="category.name"
                                            class="h-full w-full object-cover"
                                        />
                                        <div v-else class="h-full w-full flex items-center justify-center">
                                            <Layers class="h-5 w-5 text-muted-foreground" />
                                        </div>
                                    </div>
                                    <div class="flex-1 text-left">
                                        <div class="flex items-center gap-2">
                                            <span class="font-medium">{{ category.name }}</span>
                                            <Badge :variant="category.status ? 'default' : 'secondary'" class="text-xs">
                                                {{ category.status ? 'Active' : 'Inactive' }}
                                            </Badge>
                                        </div>
                                        <p v-if="category.description" class="text-sm text-muted-foreground truncate">
                                            {{ category.description }}
                                        </p>
                                    </div>
                                    <Badge variant="outline" class="gap-1">
                                        <Package class="h-3 w-3" />
                                        {{ category.products_count }} products
                                    </Badge>
                                </CollapsibleTrigger>

                                <CollapsibleContent>
                                    <div class="border-t px-4 py-3 bg-muted/30">
                                        <div v-if="category.products.length === 0" class="text-center py-4 text-sm text-muted-foreground">
                                            No products in this category.
                                        </div>
                                        <div v-else class="space-y-2">
                                            <div
                                                v-for="product in category.products"
                                                :key="product.id"
                                                class="flex items-center gap-3 p-3 rounded-lg bg-background border"
                                            >
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
                                                <div class="flex-1 min-w-0">
                                                    <div class="flex items-center gap-2">
                                                        <span class="font-medium text-sm truncate">{{ product.name }}</span>
                                                        <Badge variant="outline" class="text-xs">
                                                            {{ product.status }}
                                                        </Badge>
                                                    </div>
                                                    <div class="flex items-center gap-2 text-xs text-muted-foreground">
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
                                            </div>
                                        </div>
                                    </div>
                                </CollapsibleContent>
                            </Collapsible>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
