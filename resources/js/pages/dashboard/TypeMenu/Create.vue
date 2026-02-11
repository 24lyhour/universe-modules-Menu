<script setup lang="ts">
import { ModalForm } from '@/components/shared';
import MenuTypeForm from '@menu/Components/Dashboard/MenuTypeForm.vue';
import { useForm } from '@inertiajs/vue3';
import { useModal } from 'momentum-modal';
import { computed, watch } from 'vue';
import { toast } from 'vue-sonner';
import { menuTypeSchema } from '@menu/validation/menuTypeSchema';
import { useFormValidation } from '@/composables/useFormValidation';
import type { MenuTypeFormData, MenuTypeCreateProps } from '@menu/types';

const props = defineProps<MenuTypeCreateProps>();

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
    name: '',
    description: '',
    image_url: '',
    outlet_id: null,
    sort_order: 0,
    status: true,
});

// Use shared validation composable
const { validateForm, validateAndSubmit, createIsFormInvalid } = useFormValidation(
    menuTypeSchema,
    ['name'] // Required fields
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
watch(() => form.name, () => {
    if (form.name) validateForm(getFormData());
});

// Check if form is valid for submit button state
const isFormInvalid = createIsFormInvalid(getFormData);

const handleSubmit = () => {
    validateAndSubmit(getFormData(), form, () => {
        form.post('/dashboard/menu-types', {
            onSuccess: () => {
                toast.success('Menu type created successfully.');
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
        title="Create Menu Type"
        description="Add a new menu type"
        mode="create"
        size="xl"
        submit-text="Create Menu Type"
        :loading="form.processing"
        :disabled="isFormInvalid"
        @submit="handleSubmit"
        @cancel="handleCancel"
    >
        <MenuTypeForm v-model="form" mode="create" :outlets="props.outlets" />
    </ModalForm>
</template>
