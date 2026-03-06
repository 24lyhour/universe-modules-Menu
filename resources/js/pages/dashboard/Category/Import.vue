<script setup lang="ts">
import ImportPage, { type TableColumn } from '@/components/shared/ImportPage.vue';
import type { BreadcrumbItem } from '@/types';
import type { DuplicateOption } from '@/composables/useImport';

defineProps<{
    duplicateOptions: DuplicateOption[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Categories', href: '/dashboard/categories' },
    { title: 'Import', href: '/dashboard/categories/import' },
];

const previewColumns: TableColumn[] = [
    { key: 'name', label: 'Name', minWidth: '200px' },
    { key: 'description', label: 'Description', minWidth: '250px' },
    { key: 'sort_order', label: 'Sort Order', minWidth: '100px' },
    { key: 'status', label: 'Status', minWidth: '100px' },
];

const availableColumns = [
    'Name *',
    'Description',
    'Sort Order',
    'Status',
];
</script>

<template>
    <ImportPage
        title="Import Categories"
        entity-name="category"
        entity-name-plural="Categories"
        :breadcrumbs="breadcrumbs"
        :duplicate-options="duplicateOptions"
        preview-url="/dashboard/categories/import/preview"
        import-url="/dashboard/categories/import/process"
        list-url="/dashboard/categories"
        template-url="/dashboard/categories/import/template"
        required-fields-text="Name is required. Name is used for duplicate detection."
        :available-columns="availableColumns"
        :preview-columns="previewColumns"
    />
</template>
