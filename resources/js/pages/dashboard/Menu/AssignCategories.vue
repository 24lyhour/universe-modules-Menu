<script setup lang="ts">
import { ModalForm } from '@/components/shared';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Checkbox } from '@/components/ui/checkbox';
import { useForm } from '@inertiajs/vue3';
import { Check, Layers, Plus, Search, X } from 'lucide-vue-next';
import { useModal } from 'momentum-modal';
import { computed, ref } from 'vue';

interface AvailableCategory {
    id: number;
    name: string;
    description: string | null;
    image_url: string | null;
    products_count: number;
    current_menu_id: number | null;
}

interface Props {
    menu: {
        id: number;
        name: string;
    };
    availableCategories: AvailableCategory[];
}

const props = defineProps<Props>();

const { show, close, redirect } = useModal();

const isOpen = computed({
    get: () => show.value,
    set: (val: boolean) => {
        if (!val) {
            close();
            redirect();
        }
    },
});

// Local state for selected categories
const selectedCategoryIds = ref<number[]>([]);
const searchQuery = ref('');

// Form for submission
const form = useForm({
    category_ids: [] as number[],
});

// Filtered categories based on search
const filteredCategories = computed(() => {
    if (!searchQuery.value) return props.availableCategories;
    const query = searchQuery.value.toLowerCase();
    return props.availableCategories.filter(
        (c) =>
            c.name.toLowerCase().includes(query) ||
            (c.description && c.description.toLowerCase().includes(query))
    );
});

// Check if category is selected
const isSelected = (categoryId: number): boolean => {
    return selectedCategoryIds.value.includes(categoryId);
};

// Toggle category selection
const toggleCategory = (categoryId: number) => {
    const index = selectedCategoryIds.value.indexOf(categoryId);
    if (index === -1) {
        selectedCategoryIds.value.push(categoryId);
    } else {
        selectedCategoryIds.value.splice(index, 1);
    }
};

// Select all visible categories
const selectAll = () => {
    filteredCategories.value.forEach((c) => {
        if (!selectedCategoryIds.value.includes(c.id)) {
            selectedCategoryIds.value.push(c.id);
        }
    });
};

// Clear selection
const clearSelection = () => {
    selectedCategoryIds.value = [];
};

const handleSubmit = () => {
    form.category_ids = selectedCategoryIds.value;
    form.post(`/dashboard/menus/${props.menu.id}/categories/sync`, {
        onSuccess: () => {
            close();
            redirect();
        },
    });
};

const handleCancel = () => {
    close();
    redirect();
};
</script>

<template>
    <ModalForm
        v-model:open="isOpen"
        :title="`Add Categories to ${menu.name}`"
        :description="`Select existing categories to add to this menu`"
        mode="edit"
        size="lg"
        :submit-text="selectedCategoryIds.length > 0 ? `Add ${selectedCategoryIds.length} Categories` : 'Select Categories'"
        :loading="form.processing"
        :disabled="selectedCategoryIds.length === 0"
        @submit="handleSubmit"
        @cancel="handleCancel"
    >
        <div class="space-y-4">
            <!-- Search and Actions -->
            <div class="flex items-center gap-2">
                <div class="relative flex-1">
                    <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input
                        v-model="searchQuery"
                        placeholder="Search categories..."
                        class="pl-9"
                    />
                </div>
                <Button
                    v-if="selectedCategoryIds.length > 0"
                    type="button"
                    variant="outline"
                    size="sm"
                    @click="clearSelection"
                >
                    <X class="mr-1 h-3 w-3" />
                    Clear
                </Button>
                <Button
                    v-if="filteredCategories.length > 0 && selectedCategoryIds.length !== filteredCategories.length"
                    type="button"
                    variant="outline"
                    size="sm"
                    @click="selectAll"
                >
                    <Check class="mr-1 h-3 w-3" />
                    Select All
                </Button>
            </div>

            <!-- Categories List -->
            <ScrollArea class="h-[350px] pr-4">
                <div v-if="filteredCategories.length === 0" class="text-center py-8 text-muted-foreground">
                    {{ searchQuery ? 'No categories found.' : 'No available categories to add.' }}
                </div>

                <div v-else class="space-y-2">
                    <div
                        v-for="category in filteredCategories"
                        :key="category.id"
                        :class="[
                            'flex items-center gap-3 p-3 rounded-lg border cursor-pointer transition-colors',
                            isSelected(category.id)
                                ? 'border-primary bg-primary/5'
                                : 'hover:bg-muted/50',
                        ]"
                        @click="toggleCategory(category.id)"
                    >
                        <!-- Checkbox -->
                        <Checkbox
                            :checked="isSelected(category.id)"
                            @update:checked="toggleCategory(category.id)"
                            @click.stop
                        />

                        <!-- Category Image -->
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

                        <!-- Category Info -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="font-medium text-sm truncate">{{ category.name }}</span>
                                <Badge variant="secondary" class="text-xs">
                                    {{ category.products_count }} products
                                </Badge>
                            </div>
                            <p v-if="category.description" class="text-xs text-muted-foreground truncate">
                                {{ category.description }}
                            </p>
                            <p v-if="category.current_menu_id" class="text-xs text-amber-600">
                                Currently assigned to another menu
                            </p>
                        </div>

                        <!-- Selected indicator -->
                        <div v-if="isSelected(category.id)" class="text-primary">
                            <Check class="h-5 w-5" />
                        </div>
                    </div>
                </div>
            </ScrollArea>

            <!-- Selection Summary -->
            <div v-if="selectedCategoryIds.length > 0" class="pt-2 border-t">
                <p class="text-sm text-muted-foreground">
                    <span class="font-medium text-foreground">{{ selectedCategoryIds.length }}</span>
                    {{ selectedCategoryIds.length === 1 ? 'category' : 'categories' }} selected
                </p>
            </div>
        </div>
    </ModalForm>
</template>
