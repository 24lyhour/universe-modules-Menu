<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { ArrowLeft, Pencil, ListOrdered } from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';

interface MenuType {
    id: number;
    uuid: string;
    name: string;
    description: string | null;
    image_url: string | null;
    outlet_id: number | null;
    outlet_name: string | null;
    sort_order: number;
    status: boolean;
    created_at: string;
    updated_at: string;
}

interface MenuTypeShowProps {
    menuType: MenuType;
}

const props = defineProps<MenuTypeShowProps>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Menu Types', href: '/dashboard/menu-types' },
    { title: props.menuType.name, href: `/dashboard/menu-types/${props.menuType.id}` },
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
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="menuType.name" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Button variant="outline" size="icon" as-child>
                        <Link href="/dashboard/menu-types">
                            <ArrowLeft class="h-4 w-4" />
                        </Link>
                    </Button>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">{{ menuType.name }}</h1>
                        <p class="text-muted-foreground">Menu type details</p>
                    </div>
                </div>
                <Button as-child>
                    <Link :href="`/dashboard/menu-types/${menuType.id}/edit`">
                        <Pencil class="mr-2 h-4 w-4" />
                        Edit Menu Type
                    </Link>
                </Button>
            </div>

            <!-- Content -->
            <div class="grid gap-6 md:grid-cols-2">
                <!-- Details Card -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <ListOrdered class="h-5 w-5" />
                            Menu Type Information
                        </CardTitle>
                        <CardDescription>Basic details about this menu type</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">Name</p>
                                <p class="text-sm">{{ menuType.name }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">Status</p>
                                <Badge :variant="menuType.status ? 'default' : 'secondary'">
                                    {{ menuType.status ? 'Active' : 'Inactive' }}
                                </Badge>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">Outlet</p>
                                <p class="text-sm">{{ menuType.outlet_name || '-' }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">Sort Order</p>
                                <p class="text-sm">{{ menuType.sort_order }}</p>
                            </div>
                        </div>
                        <div v-if="menuType.description">
                            <p class="text-sm font-medium text-muted-foreground">Description</p>
                            <p class="text-sm">{{ menuType.description }}</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Image & Meta Card -->
                <Card>
                    <CardHeader>
                        <CardTitle>Image & Metadata</CardTitle>
                        <CardDescription>Menu type image and timestamps</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div v-if="menuType.image_url" class="overflow-hidden rounded-lg">
                            <img
                                :src="menuType.image_url"
                                :alt="menuType.name"
                                class="h-48 w-full object-cover"
                            />
                        </div>
                        <div v-else class="flex h-48 items-center justify-center rounded-lg bg-muted">
                            <ListOrdered class="h-12 w-12 text-muted-foreground" />
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">Created</p>
                                <p class="text-sm">{{ formatDate(menuType.created_at) }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">Updated</p>
                                <p class="text-sm">{{ formatDate(menuType.updated_at) }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
