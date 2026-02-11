<script setup lang="ts">
import { ModalForm } from '@/components/shared';
import MenuForm from '../../../Components/Dashboard/MenuForm.vue';
import { useForm } from '@inertiajs/vue3';
import { useModal } from 'momentum-modal';
import { computed, watch } from 'vue';
import { menuSchema } from '../../../validation/menuSchema';
import { useFormValidation } from '@/composables/useFormValidation';
import type { MenuFormData } from '../../../types';

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

const form = useForm<MenuFormData>({
    name: '',
    description: '',
    image_url: '',
    status: true,
    schedule_mode: '',
    schedule_days: '',
    schedule_start_time: '',
    schedule_end_time: '',
    schedule_start_date: '',
    schedule_end_date: '',
    schedule_status: false,
});

// Use shared validation composable
const { validateForm, validateAndSubmit, createIsFormInvalid } = useFormValidation(
    menuSchema,
    ['name'] // Required fields
);

// Get form data for validation
const getFormData = () => ({
    name: form.name,
    description: form.description || null,
    image_url: form.image_url || null,
    status: form.status,
    schedule_mode: form.schedule_mode || null,
    schedule_days: form.schedule_days || null,
    schedule_start_time: form.schedule_start_time || null,
    schedule_end_time: form.schedule_end_time || null,
    schedule_start_date: form.schedule_start_date || null,
    schedule_end_date: form.schedule_end_date || null,
    schedule_status: form.schedule_status || null,
});

// Watch form changes to validate in real-time
watch(() => form.name, () => {
    if (form.name) validateForm(getFormData());
});

// Check if form is valid for submit button state
const isFormInvalid = createIsFormInvalid(getFormData);

const handleSubmit = () => {
    validateAndSubmit(getFormData(), form, () => {
        form.post('/dashboard/menus', {
            onSuccess: () => {
                close();
                redirect();
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
        title="Create Menu"
        description="Add a new menu to your business"
        mode="create"
        size="xl"
        submit-text="Create Menu"
        :loading="form.processing"
        :disabled="isFormInvalid"
        @submit="handleSubmit"
        @cancel="handleCancel"
    >
        <MenuForm v-model="form" mode="create" />
    </ModalForm>
</template>
