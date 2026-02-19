<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { ArrowLeft, Pencil, UtensilsCrossed, Layers, Calendar, Clock } from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';
import type { MenuShowProps } from '@menu/types';

const props = defineProps<MenuShowProps>();

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
            </div>
        </div>
    </AppLayout>
</template>
