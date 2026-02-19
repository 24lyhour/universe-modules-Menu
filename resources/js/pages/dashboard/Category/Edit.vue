<script setup lang="ts">
import { ModalForm } from '@/components/shared';
import { useForm } from '@inertiajs/vue3';
import { useModal } from 'momentum-modal';
import { computed, watch } from 'vue';
import { toast } from 'vue-sonner';
import CategoryForm from '@menu/Components/Dashboard/CategoryForm.vue';
import { categorySchema } from '@menu/validation/categorySchema';
import { useFormValidation } from '@/composables/useFormValidation';
import type { CategoryFormData, CategoryEditProps } from '@menu/types';

const props = defineProps<CategoryEditProps>();

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
    name: props.category.name,
    description: props.category.description || '',
    menu_id: props.category.menu_id,
    image_url: props.category.image_url || '',
    product_type: props.category.product_type || null,
    sort_order: props.category.sort_order,
    status: props.category.status,
});

// Use shared validation composable
const { validateForm, validateAndSubmit, createIsFormInvalid } = useFormValidation(
    categorySchema,
    ['name'] // Required fields
);

// Get form data for validation
const getFormData = () => ({
    name: form.name,
    description: form.description || null,
    menu_id: form.menu_id,
    image_url: form.image_url || null,
    product_type: form.product_type,
    sort_order: form.sort_order,
    status: form.status,
});

// Watch form changes to validate in real-time
watch(() => form.name, () => validateForm(getFormData()));

// Check if form is valid for submit button state
const isFormInvalid = createIsFormInvalid(getFormData);

const handleSubmit = () => {
    validateAndSubmit(getFormData(), form, () => {
        form.put(`/dashboard/categories/${props.category.id}`, {
            onSuccess: () => {
                toast.success('Category updated successfully.');
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
        title="Edit Category"
        description="Update category information"
        mode="edit"
        size="xl"
        submit-text="Save Changes"
        :loading="form.processing"
        :disabled="isFormInvalid"
        @submit="handleSubmit"
        @cancel="handleCancel"
    >
        <CategoryForm v-model="form" mode="edit" :menus="props.menus" />
    </ModalForm>
</template>
