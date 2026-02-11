<script setup lang="ts">
import { ModalForm } from '@/components/shared';
import MenuForm from '../../../Components/Dashboard/MenuForm.vue';
import { useForm } from '@inertiajs/vue3';
import { useModal } from 'momentum-modal';
import { computed } from 'vue';
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

const handleSubmit = () => {
    form.post('/dashboard/menus', {
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
        title="Create Menu"
        description="Add a new menu to your business"
        mode="create"
        size="xl"
        submit-text="Create Menu"
        :loading="form.processing"
        @submit="handleSubmit"
        @cancel="handleCancel"
    >
        <MenuForm v-model="form" mode="create" />
    </ModalForm>
</template>
