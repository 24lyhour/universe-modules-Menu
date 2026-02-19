<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { ArrowLeft, Search, Layers, Save } from 'lucide-vue-next';
import { toast } from 'vue-sonner';
import type { BreadcrumbItem } from '@/types';

interface Category {
    id: number;
    name: string;
    description: string | null;
    image_url: string | null;
    status: boolean;
    products_count?: number;
}

interface Menu {
    id: number;
    name: string;
}

interface Props {
    menu: Menu;
    availableCategories: Category[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Menus', href: '/dashboard/menus' },
    { title: props.menu.name, href: `/dashboard/menus/${props.menu.id}` },
    { title: 'Assign Categories', href: `/dashboard/menus/${props.menu.id}/categories/assign` },
];

const search = ref('');
const selectedCategories = ref<number[]>([]);
const isSaving = ref(false);

const filteredCategories = computed(() => {
    if (!search.value) return props.availableCategories;
    const query = search.value.toLowerCase();
    return props.availableCategories.filter(
        cat => cat.name.toLowerCase().includes(query) ||
               (cat.description && cat.description.toLowerCase().includes(query))
    );
});

const toggleCategory = (categoryId: number) => {
    const index = selectedCategories.value.indexOf(categoryId);
    if (index === -1) {
        selectedCategories.value.push(categoryId);
    } else {
        selectedCategories.value.splice(index, 1);
    }
};

const isSelected = (categoryId: number) => {
    return selectedCategories.value.includes(categoryId);
};

const handleSave = () => {
    if (selectedCategories.value.length === 0) {
        toast.error('Please select at least one category');
        return;
    }

    isSaving.value = true;
    router.post(`/dashboard/menus/${props.menu.id}/categories/assign`, {
        category_ids: selectedCategories.value,
    }, {
        onSuccess: () => {
            toast.success('Categories assigned successfully');
            router.visit(`/dashboard/menus/${props.menu.id}/categories/manage`);
        },
        onError: () => {
            toast.error('Failed to assign categories');
        },
        onFinish: () => {
            isSaving.value = false;
        },
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`${menu.name} - Assign Categories`" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Button variant="outline" size="icon" as-child>
                        <Link :href="`/dashboard/menus/${menu.id}/categories/manage`">
                            <ArrowLeft class="h-4 w-4" />
                        </Link>
                    </Button>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">Assign Categories to {{ menu.name }}</h1>
                        <p class="text-muted-foreground">Select existing categories to add to this menu</p>
                    </div>
                </div>
                <Button @click="handleSave" :disabled="isSaving || selectedCategories.length === 0">
                    <Save class="mr-2 h-4 w-4" />
                    {{ isSaving ? 'Saving...' : `Assign ${selectedCategories.length} Categories` }}
                </Button>
            </div>

            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <div>
                            <CardTitle>Available Categories</CardTitle>
                            <CardDescription>
                                {{ selectedCategories.length }} of {{ availableCategories.length }} selected
                            </CardDescription>
                        </div>
                        <div class="relative w-64">
                            <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                            <Input
                                v-model="search"
                                placeholder="Search categories..."
                                class="pl-9"
                            />
                        </div>
                    </div>
                </CardHeader>
                <CardContent>
                    <div v-if="filteredCategories.length === 0" class="text-center py-12 text-muted-foreground">
                        <Layers class="h-12 w-12 mx-auto mb-4 opacity-50" />
                        <p>No categories available to assign</p>
                    </div>

                    <div v-else class="grid gap-3 md:grid-cols-2 lg:grid-cols-3">
                        <div
                            v-for="category in filteredCategories"
                            :key="category.id"
                            class="flex items-center gap-4 p-4 rounded-lg border cursor-pointer transition-colors"
                            :class="isSelected(category.id) ? 'border-primary bg-primary/5' : 'hover:bg-muted/50'"
                            @click="toggleCategory(category.id)"
                        >
                            <Checkbox
                                :checked="isSelected(category.id)"
                                @update:checked="toggleCategory(category.id)"
                            />
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
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="font-medium truncate">{{ category.name }}</span>
                                    <Badge :variant="category.status ? 'default' : 'secondary'" class="text-xs">
                                        {{ category.status ? 'Active' : 'Inactive' }}
                                    </Badge>
                                </div>
                                <p v-if="category.description" class="text-sm text-muted-foreground truncate">
                                    {{ category.description }}
                                </p>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
