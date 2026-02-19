<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import type { BreadcrumbItem } from '@/types';

interface MenuType {
    id: number;
    name: string;
    description?: string;
    is_active: boolean;
    sort_order: number;
    created_at: string;
}

interface Props {
    menuType: MenuType;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Menu Types', href: '/dashboard/menu-types' },
    { title: props.menuType.name, href: `/dashboard/menu-types/${props.menuType.id}` },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="menuType.name" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center gap-4">
                <Button variant="ghost" size="icon" as-child>
                    <Link href="/dashboard/menu-types">
                        <ArrowLeft class="h-4 w-4" />
                    </Link>
                </Button>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">{{ menuType.name }}</h1>
                    <p class="text-muted-foreground">Menu type details</p>
                </div>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Menu Type Information</CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div>
                        <span class="text-sm text-muted-foreground">Name:</span>
                        <p class="font-medium">{{ menuType.name }}</p>
                    </div>
                    <div v-if="menuType.description">
                        <span class="text-sm text-muted-foreground">Description:</span>
                        <p>{{ menuType.description }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-muted-foreground">Status:</span>
                        <Badge :variant="menuType.is_active ? 'default' : 'secondary'" class="ml-2">
                            {{ menuType.is_active ? 'Active' : 'Inactive' }}
                        </Badge>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
