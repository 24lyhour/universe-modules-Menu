<script setup lang="ts">
import { ModalForm } from '@/components/shared';
import CategoryForm from '@menu/Components/Dashboard/CategoryForm.vue';
import { useForm } from '@inertiajs/vue3';
import { useModal } from 'momentum-modal';
import { computed, watch } from 'vue';
import { categorySchema } from '@menu/validation/categorySchema';
import { useFormValidation } from '@/composables/useFormValidation';
import { toast } from 'vue-sonner';
import type { CategoryFormData, CategoryCreateProps } from '@menu/types';

const props = defineProps<CategoryCreateProps>();

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

const form = useForm<CategoryFormData>({
    name: '',
    description: '',
    menu_id: props.selectedMenuId ?? null,
    image_url: '',
    product_type: null,
    sort_order: 0,
    status: true,
});

// Use shared validation composable
const { validateForm, validateAndSubmit, createIsFormInvalid } = useFormValidation(
    categorySchema,
    ['name'] // Required fields
);
/**
 * 
Get form data for validation

 */
const getFormData = () => ({
    name: form.name,
    description: form.description || null,
    menu_id: form.menu_id,
    image_url: form.image_url || null,
    product_type: form.product_type,
    sort_order: form.sort_order,
    status: form.status,
});

watch(() => form.name, () => {
    if (form.name) validateForm(getFormData());
});

// Check if form is valid for submit button state
const isFormInvalid = createIsFormInvalid(getFormData);

const handleSubmit = () => {
    validateAndSubmit(getFormData(), form, () => {
        form.post('/dashboard/categories', {
            onSuccess: () => {
                toast.success('Category created successfully.');
                setTimeout(() => {
                    close();
                    redirect();
                }, 100);
            },
        });
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
        title="Create Category"
        description="Add a new category"
        mode="create"
        size="xl"
        submit-text="Create Category"
        :loading="form.processing"
        :disabled="isFormInvalid"
        @submit="handleSubmit"
        @cancel="handleCancel"
    >
        <CategoryForm v-model="form" mode="create" :menus="props.menus" />
    </ModalForm>
</template>
