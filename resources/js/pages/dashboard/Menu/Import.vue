<script setup lang="ts">
import ImportPage, { type TableColumn } from '@/components/shared/ImportPage.vue';
import type { BreadcrumbItem } from '@/types';
import type { DuplicateOption } from '@/composables/useImport';

defineProps<{
    duplicateOptions: DuplicateOption[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Menus', href: '/dashboard/menus' },
    { title: 'Import', href: '/dashboard/menus/import' },
];

const previewColumns: TableColumn[] = [
    { key: 'name', label: 'Name', minWidth: '200px' },
    { key: 'description', label: 'Description', minWidth: '250px' },
    { key: 'status', label: 'Status', minWidth: '100px' },
];

const availableColumns = [
    'Name *',
    'Description',
    'Status',
];
</script>

<template>
    <ImportPage
        title="Import Menus"
        entity-name="menu"
        entity-name-plural="Menus"
        :breadcrumbs="breadcrumbs"
        :duplicate-options="duplicateOptions"
        preview-url="/dashboard/menus/import/preview"
        import-url="/dashboard/menus/import/process"
        list-url="/dashboard/menus"
        template-url="/dashboard/menus/import/template"
        required-fields-text="Name is required. Name is used for duplicate detection."
        :available-columns="availableColumns"
        :preview-columns="previewColumns"
    />
</template>
