<script setup lang="ts">
import { ModalForm } from '@/components/shared';
import { useForm } from '@inertiajs/vue3';
import { useModal } from 'momentum-modal';
import { computed, watch } from 'vue';
import { toast } from 'vue-sonner';
import MenuTypeForm from '@menu/Components/Dashboard/MenuTypeForm.vue';
import { menuTypeSchema } from '@menu/validation/menuTypeSchema';
import { useFormValidation } from '@/composables/useFormValidation';
import type { MenuTypeFormData, MenuTypeEditProps } from '@menu/types';

const props = defineProps<MenuTypeEditProps>();

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

const form = useForm<MenuTypeFormData>({
    name: props.menuType.name,
    description: props.menuType.description || '',
    image_url: props.menuType.image_url || '',
    outlet_id: props.menuType.outlet_id,
    sort_order: props.menuType.sort_order,
    status: props.menuType.status,
});

// Use shared validation composable
const { validateForm, validateAndSubmit, createIsFormInvalid } = useFormValidation(
    menuTypeSchema,
    ['name', 'outlet_id'] // Required fields
);

// Get form data for validation
const getFormData = () => ({
    name: form.name,
    description: form.description || null,
    image_url: form.image_url || null,
    outlet_id: form.outlet_id,
    sort_order: form.sort_order,
    status: form.status,
});

// Watch form changes to validate in real-time
watch([() => form.name, () => form.outlet_id], () => {
    validateForm(getFormData());
});

// Check if form is valid for submit button state
const isFormInvalid = createIsFormInvalid(getFormData);

const handleSubmit = () => {
    validateAndSubmit(getFormData(), form, () => {
        form.put(`/dashboard/menu-types/${props.menuType.id}`, {
            onSuccess: () => {
                toast.success('Menu type updated successfully.');
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
        title="Edit Menu Type"
        description="Update menu type information"
        mode="edit"
        size="xl"
        submit-text="Save Changes"
        :loading="form.processing"
        :disabled="isFormInvalid"
        @submit="handleSubmit"
        @cancel="handleCancel"
    >
        <MenuTypeForm v-model="form" mode="edit" :outlets="props.outlets" />
    </ModalForm>
</template>
