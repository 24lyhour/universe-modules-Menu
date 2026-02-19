<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Package } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import type { BreadcrumbItem } from '@/types';

interface Category {
    id: number;
    name: string;
    description?: string;
    is_active: boolean;
    sort_order: number;
    products_count?: number;
    created_at: string;
}

interface Props {
    category: Category;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Categories', href: '/dashboard/categories' },
    { title: props.category.name, href: `/dashboard/categories/${props.category.id}` },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="category.name" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Button variant="ghost" size="icon" as-child>
                        <Link href="/dashboard/categories">
                            <ArrowLeft class="h-4 w-4" />
                        </Link>
                    </Button>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">{{ category.name }}</h1>
                        <p class="text-muted-foreground">Category details</p>
                    </div>
                </div>
                <Button as-child>
                    <Link :href="`/dashboard/categories/${category.id}/products/manage`">
                        <Package class="mr-2 h-4 w-4" />
                        Manage Products
                    </Link>
                </Button>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Category Information</CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div>
                        <span class="text-sm text-muted-foreground">Name:</span>
                        <p class="font-medium">{{ category.name }}</p>
                    </div>
                    <div v-if="category.description">
                        <span class="text-sm text-muted-foreground">Description:</span>
                        <p>{{ category.description }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-muted-foreground">Products:</span>
                        <Badge variant="secondary" class="ml-2">
                            {{ category.products_count ?? 0 }} products
                        </Badge>
                    </div>
                    <div>
                        <span class="text-sm text-muted-foreground">Status:</span>
                        <Badge :variant="category.is_active ? 'default' : 'secondary'" class="ml-2">
                            {{ category.is_active ? 'Active' : 'Inactive' }}
                        </Badge>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
