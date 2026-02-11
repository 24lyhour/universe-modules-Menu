<script setup lang="ts">
import { ModalForm } from '@/components/shared';
import { useForm } from '@inertiajs/vue3';
import { useModal } from 'momentum-modal';
import { computed } from 'vue';
import MenuForm from '../../../Components/Dashboard/MenuForm.vue';
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

const handleSubmit = () => {
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
        @submit="handleSubmit"
        @cancel="handleCancel"
    >
        <MenuForm v-model="form" mode="edit" />
    </ModalForm>
</template>
