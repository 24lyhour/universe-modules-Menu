<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import type { BreadcrumbItem } from '@/types';

interface Menu {
    id: number;
    name: string;
    description?: string;
    is_active: boolean;
    sort_order: number;
    created_at: string;
}

interface Props {
    menu: Menu;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Menus', href: '/dashboard/menus' },
    { title: props.menu.name, href: `/dashboard/menus/${props.menu.id}` },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="menu.name" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center gap-4">
                <Button variant="ghost" size="icon" as-child>
                    <Link href="/dashboard/menus">
                        <ArrowLeft class="h-4 w-4" />
                    </Link>
                </Button>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">{{ menu.name }}</h1>
                    <p class="text-muted-foreground">Menu details</p>
                </div>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Menu Information</CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div>
                        <span class="text-sm text-muted-foreground">Name:</span>
                        <p class="font-medium">{{ menu.name }}</p>
                    </div>
                    <div v-if="menu.description">
                        <span class="text-sm text-muted-foreground">Description:</span>
                        <p>{{ menu.description }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-muted-foreground">Status:</span>
                        <Badge :variant="menu.is_active ? 'default' : 'secondary'" class="ml-2">
                            {{ menu.is_active ? 'Active' : 'Inactive' }}
                        </Badge>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
