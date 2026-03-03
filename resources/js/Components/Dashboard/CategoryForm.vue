<script setup lang="ts">
import { computed } from 'vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Separator } from '@/components/ui/separator';
import { ImageUpload } from '@/components/shared';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import type { InertiaForm } from '@inertiajs/vue3';
import type { CategoryFormData, MenuOption, ProductType } from '@menu/types';
import TiptapEditor from '@/components/TiptapEditor.vue';

// Product type options
const productTypeOptions: { value: ProductType; label: string }[] = [
    { value: 'phone', label: 'Phone' },
    { value: 'computer', label: 'Computer' },
    { value: 'tablet', label: 'Tablet' },
    { value: 'accessory', label: 'Accessory' },
    { value: 'other', label: 'Other' },
];


interface Props {
    mode?: 'create' | 'edit';
    menus?: MenuOption[];
}

const props = withDefaults(defineProps<Props>(), {
    mode: 'create',
    menus: () => [],
});

const model = defineModel<InertiaForm<CategoryFormData>>({ required: true });

// Convert image_url string to array for ImageUpload component
const categoryImages = computed({
    get: () => model.value.image_url ? [model.value.image_url] : [],
    set: (value: string[]) => {
        model.value.image_url = value.length > 0 ? value[0] : '';
    },
});

// Status is already boolean, but we use computed for Switch component
const isActive = computed({
    get: () => model.value.status,
    set: (value: boolean) => {
        model.value.status = value;
    },
});

// Handle menu selection - use undefined for no selection (not empty string)
const selectedMenu = computed({
    get: () => model.value.menu_id?.toString() ?? undefined,
    set: (value: string | number | boolean | bigint | Record<string, unknown> | null | undefined) => {
        if (typeof value === 'string' && value !== '') {
            model.value.menu_id = parseInt(value, 10);
        } else {
            model.value.menu_id = null;
        }
    },
});

// Handle product type selection
const selectedProductType = computed({
    get: () => model.value.product_type ?? undefined,
    set: (value: string | number | boolean | bigint | Record<string, unknown> | null | undefined) => {
        if (typeof value === 'string' && value !== '') {
            model.value.product_type = value as ProductType;
        } else {
            model.value.product_type = null;
        }
    },
});
</script>

<template>
    <div class="space-y-6">
        <!-- Basic Information Section -->
        <div class="space-y-4">
            <div>
                <h3 class="text-sm font-medium">Basic Information</h3>
                <p class="text-sm text-muted-foreground">
                    {{ mode === 'create' ? 'Enter the category details' : 'Update the category details' }}
                </p>
            </div>
            <Separator />

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="space-y-2 sm:col-span-2">
                    <Label for="name">Category Name <span class="text-destructive">*</span></Label>
                    <Input
                        id="name"
                        v-model="model.name"
                        type="text"
                        placeholder="Enter category name"
                    />
                    <p v-if="model.errors.name" class="text-sm text-destructive">
                        {{ model.errors.name }}
                    </p>
                </div>

                <div class="space-y-2 sm:col-span-2">
                    <Label for="description">Description</Label>
                    <TiptapEditor
                        id="description"
                        v-model="model.description"
                        placeholder="Enter category description"
                        min-height="250px"
                        max-height="400"
                    />
                    <p v-if="model.errors.description" class="text-sm text-destructive">
                        {{ model.errors.description }}
                    </p>
                </div>

                <div class="space-y-2">
                    <Label for="menu_id">Menu <span class="text-destructive">*</span></Label>
                    <Select v-model="selectedMenu">
                        <SelectTrigger>
                            <SelectValue placeholder="Select a menu" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="menu in props.menus"
                                :key="menu.id"
                                :value="menu.id.toString()"
                            >
                                {{ menu.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <p v-if="model.errors.menu_id" class="text-sm text-destructive">
                        {{ model.errors.menu_id }}
                    </p>
                </div>

                <div class="space-y-2">
                    <Label for="product_type">Product Type <span class="text-destructive">*</span></Label>
                    <Select v-model="selectedProductType">
                        <SelectTrigger>
                            <SelectValue placeholder="Select product type" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="option in productTypeOptions"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <p class="text-xs text-muted-foreground">
                        Products of this type will be filtered to this category
                    </p>
                    <p v-if="model.errors.product_type" class="text-sm text-destructive">
                        {{ model.errors.product_type }}
                    </p>
                </div>

                <div class="space-y-2">
                    <Label for="sort_order">Sort Order</Label>
                    <Input
                        id="sort_order"
                        v-model.number="model.sort_order"
                        type="number"
                        min="0"
                        placeholder="0"
                    />
                    <p v-if="model.errors.sort_order" class="text-sm text-destructive">
                        {{ model.errors.sort_order }}
                    </p>
                </div>

                <div class="space-y-2">
                    <Label for="status">Status <span class="text-destructive">*</span></Label>
                    <div class="flex items-center space-x-2 pt-2">
                        <Switch id="status" v-model="isActive" />
                        <Label for="status" class="font-normal">
                            {{ isActive ? 'Active' : 'Inactive' }}
                        </Label>
                    </div>
                    <p v-if="model.errors.status" class="text-sm text-destructive">
                        {{ model.errors.status }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Image Section -->
        <div class="space-y-4">
            <div>
                <h3 class="text-sm font-medium">Category Image</h3>
                <p class="text-sm text-muted-foreground">Upload category image</p>
            </div>
            <Separator />

            <ImageUpload
                v-model="categoryImages"
                label=""
                :multiple="false"
                :max-files="1"
                :max-size="5"
                :error="model.errors.image_url"
            />
        </div>
    </div>
</template>
