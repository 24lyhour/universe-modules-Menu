<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useModal } from 'momentum-modal';
import { ModalForm } from '@/components/shared';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import { AlertTriangle, Layers } from 'lucide-vue-next';
import { toast } from 'vue-sonner';

interface CategoryItemData {
    id: number;
    uuid: string;
    name: string;
    status: boolean;
    products_count?: number;
}

interface Props {
    categoryItemsData: CategoryItemData[];
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

const confirmDelete = ref(false);

const form = useForm({
    uuids: props.categoryItemsData.map((c) => c.uuid),
});

watch(confirmDelete, () => {
    form.clearErrors();
});

const hasProducts = computed(() => {
    return props.categoryItemsData.some((c) => (c.products_count ?? 0) > 0);
});

const totalProducts = computed(() => {
    return props.categoryItemsData.reduce((sum, c) => sum + (c.products_count ?? 0), 0);
});

const canSubmit = computed(() => confirmDelete.value === true);

const handleSubmit = () => {
    form.delete('/dashboard/categories/bulk-delete', {
        preserveScroll: true,
        onSuccess: () => {
            toast.success(`${props.categoryItemsData.length} category(ies) moved to trash.`);
            setTimeout(() => {
                close();
                redirect();
            }, 100);
        },
        onError: (errors) => {
            if (errors.uuids) {
                toast.error(errors.uuids);
            }
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
        :title="`Delete ${categoryItemsData.length} Categor${categoryItemsData.length > 1 ? 'ies' : 'y'}`"
        description="This action will move the selected categories to trash"
        mode="delete"
        size="md"
        :submit-text="`Delete ${categoryItemsData.length} Categor${categoryItemsData.length > 1 ? 'ies' : 'y'}`"
        :loading="form.processing"
        :disabled="!canSubmit"
        @submit="handleSubmit"
        @cancel="handleCancel"
    >
        <div class="space-y-6">
            <!-- Category List -->
            <div class="space-y-2">
                <p class="text-sm font-medium text-muted-foreground">
                    The following categories will be deleted:
                </p>
                <div class="max-h-48 space-y-2 overflow-y-auto rounded-lg border p-3">
                    <div
                        v-for="category in categoryItemsData"
                        :key="category.uuid"
                        class="flex items-center gap-3 rounded-md bg-muted/30 p-2"
                    >
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary/10">
                            <Layers class="h-4 w-4 text-primary" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium">{{ category.name }}</p>
                            <p class="truncate text-xs text-muted-foreground">
                                {{ category.status ? 'Active' : 'Inactive' }}
                                <span v-if="(category.products_count ?? 0) > 0" class="ml-2">
                                    ({{ category.products_count }} products)
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Warning Banner -->
            <div class="flex items-start gap-3 rounded-lg border border-destructive/50 bg-destructive/10 p-4">
                <AlertTriangle class="mt-0.5 h-5 w-5 shrink-0 text-destructive" />
                <div class="space-y-1">
                    <p class="text-sm font-medium text-destructive">
                        You are about to delete {{ categoryItemsData.length }} categor{{ categoryItemsData.length > 1 ? 'ies' : 'y' }}
                    </p>
                    <p class="text-sm text-muted-foreground">
                        <template v-if="hasProducts">
                            This will also affect {{ totalProducts }} product(s) associated with these categories.
                        </template>
                        Items will be moved to trash and can be restored later.
                    </p>
                </div>
            </div>

            <!-- Confirmation Checkbox -->
            <div class="flex items-start space-x-3 rounded-lg border p-4">
                <Checkbox
                    id="bulk-confirmed"
                    :model-value="confirmDelete"
                    @update:model-value="(val: boolean | 'indeterminate') => confirmDelete = val === true"
                />
                <div class="space-y-1">
                    <Label for="bulk-confirmed" class="cursor-pointer font-medium">
                        I confirm this bulk deletion
                    </Label>
                    <p class="text-sm text-muted-foreground">
                        I understand that {{ categoryItemsData.length }} categor{{ categoryItemsData.length > 1 ? 'ies' : 'y' }} will be deleted.
                    </p>
                </div>
            </div>

            <p v-if="form.errors.uuids" class="text-sm text-destructive">
                {{ form.errors.uuids }}
            </p>
        </div>
    </ModalForm>
</template>
