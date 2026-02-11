<script setup lang="ts">
import { computed } from 'vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Switch } from '@/components/ui/switch';
import { Separator } from '@/components/ui/separator';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { ImageUpload } from '@/components/shared';
import type { InertiaForm } from '@inertiajs/vue3';
import type { MenuFormData, OutletOption, MenuTypeOption } from '../../types';

interface Props {
    mode?: 'create' | 'edit';
    outlets?: OutletOption[];
    menuTypes?: MenuTypeOption[];
}

const props = withDefaults(defineProps<Props>(), {
    mode: 'create',
    outlets: () => [],
    menuTypes: () => [],
});

const model = defineModel<InertiaForm<MenuFormData>>({ required: true });

// Computed for outlet select - return undefined to show placeholder when no selection
const selectedOutlet = computed({
    get: () => model.value.outlet_id?.toString(),
    set: (value: string | undefined) => {
        model.value.outlet_id = value ? parseInt(value) : null;
    },
});

// Computed for menu type select - return undefined to show placeholder when no selection
const selectedMenuType = computed({
    get: () => model.value.menu_type_id?.toString(),
    set: (value: string | undefined) => {
        model.value.menu_type_id = value ? parseInt(value) : null;
    },
});

// Convert image_url string to array for ImageUpload component
const menuImages = computed({
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
</script>

<template>
    <div class="space-y-6">
        <!-- Basic Information Section -->
        <div class="space-y-4">
            <div>
                <h3 class="text-sm font-medium">Basic Information</h3>
                <p class="text-sm text-muted-foreground">
                    {{ mode === 'create' ? 'Enter the menu details' : 'Update the menu details' }}
                </p>
            </div>
            <Separator />

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="space-y-2 sm:col-span-2">
                    <Label for="name">Menu Name <span class="text-destructive">*</span></Label>
                    <Input
                        id="name"
                        v-model="model.name"
                        type="text"
                        placeholder="Enter menu name"
                    />
                    <p v-if="model.errors.name" class="text-sm text-destructive">
                        {{ model.errors.name }}
                    </p>
                </div>

                <div class="space-y-2">
                    <Label for="outlet_id">Outlet</Label>
                    <Select v-model="selectedOutlet">
                        <SelectTrigger id="outlet_id">
                            <SelectValue placeholder="Select an outlet" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="outlet in props.outlets"
                                :key="outlet.id"
                                :value="outlet.id.toString()"
                            >
                                {{ outlet.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <p v-if="model.errors.outlet_id" class="text-sm text-destructive">
                        {{ model.errors.outlet_id }}
                    </p>
                </div>

                <div class="space-y-2">
                    <Label for="menu_type_id">Menu Type</Label>
                    <Select v-model="selectedMenuType">
                        <SelectTrigger id="menu_type_id">
                            <SelectValue placeholder="Select a menu type" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="menuType in props.menuTypes"
                                :key="menuType.id"
                                :value="menuType.id.toString()"
                            >
                                {{ menuType.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <p v-if="model.errors.menu_type_id" class="text-sm text-destructive">
                        {{ model.errors.menu_type_id }}
                    </p>
                </div>

                <div class="space-y-2 sm:col-span-2">
                    <Label for="description">Description</Label>
                    <Textarea
                        id="description"
                        v-model="model.description"
                        placeholder="Enter menu description"
                        rows="3"
                    />
                    <p v-if="model.errors.description" class="text-sm text-destructive">
                        {{ model.errors.description }}
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
                <h3 class="text-sm font-medium">Menu Image</h3>
                <p class="text-sm text-muted-foreground">Upload menu image</p>
            </div>
            <Separator />

            <ImageUpload
                v-model="menuImages"
                label=""
                :multiple="false"
                :max-files="1"
                :max-size="5"
                :error="model.errors.image_url"
            />
        </div>
    </div>
</template>
