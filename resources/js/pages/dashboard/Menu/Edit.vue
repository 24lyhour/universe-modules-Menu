<script setup lang="ts">
import { ModalForm } from '@/components/shared';
import { useForm } from '@inertiajs/vue3';
import { useModal } from 'momentum-modal';
import { computed, ref, watch } from 'vue';
import MenuForm from '../../../Components/Dashboard/MenuForm.vue';
import { menuSchema } from '../../../validation/menuSchema';
import type { MenuFormData, MenuEditProps } from '../../../types';

const props = defineProps<MenuEditProps>();

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
    name: props.menu.name,
    description: props.menu.description || '',
    image_url: props.menu.image_url || '',
    status: props.menu.status,
    schedule_mode: props.menu.schedule_mode || '',
    schedule_days: props.menu.schedule_days || '',
    schedule_start_time: props.menu.schedule_start_time || '',
    schedule_end_time: props.menu.schedule_end_time || '',
    schedule_start_date: props.menu.schedule_start_date || '',
    schedule_end_date: props.menu.schedule_end_date || '',
    schedule_status: props.menu.schedule_status || false,
});

// Client-side validation errors
const validationErrors = ref<Record<string, string>>({});

// Validate form using Zod schema
const validateForm = () => {
    const result = menuSchema.safeParse({
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

    if (!result.success) {
        const errors: Record<string, string> = {};
        result.error.errors.forEach((err) => {
            const field = err.path[0] as string;
            if (!errors[field]) {
                errors[field] = err.message;
            }
        });
        validationErrors.value = errors;
        return false;
    }

    validationErrors.value = {};
    return true;
};

// Watch form changes to validate in real-time
watch(
    () => form.name,
    () => {
        validateForm();
    }
);

// Check if form is valid for submit button state
const isFormInvalid = computed(() => {
    return !form.name || form.name.trim() === '';
});

const handleSubmit = () => {
    // Validate before submit
    if (!validateForm()) {
        // Copy validation errors to Inertia form errors for display
        Object.keys(validationErrors.value).forEach((key) => {
            form.setError(key as keyof MenuFormData, validationErrors.value[key]);
        });
        return;
    }

    form.put(`/dashboard/menus/${props.menu.id}`, {
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
        title="Edit Menu"
        description="Update menu information"
        mode="edit"
        size="xl"
        submit-text="Save Changes"
        :loading="form.processing"
        :disabled="isFormInvalid"
        @submit="handleSubmit"
        @cancel="handleCancel"
    >
        <MenuForm v-model="form" mode="edit" />
    </ModalForm>
</template>
