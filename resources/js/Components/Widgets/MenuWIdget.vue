<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { ChartContainer } from '@/components/ui/chart';
import {
    VisXYContainer,
    VisStackedBar,
    VisAxis,
} from '@unovis/vue';
import {
    UtensilsCrossed,
    CheckCircle,
    XCircle,
    Layers,
    FolderTree,
    RefreshCw,
    Calendar,
} from 'lucide-vue-next';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Button } from '@/components/ui/button';
import { useChartColors } from '@/composables/useChartColors';

export interface MenuMetrics {
    total_menus: number;
    active_menus: number;
    inactive_menus: number;
    total_categories: number;
    total_types: number;
}

export interface MenuWidgetProps {
    metrics: MenuMetrics;
    dateRange?: string;
    loading?: boolean;
}

const props = withDefaults(defineProps<MenuWidgetProps>(), {
    dateRange: '30d',
    loading: false,
});

const emit = defineEmits<{
    (e: 'dateRangeChange', value: string): void;
    (e: 'refresh'): void;
}>();

const selectedDateRange = ref(props.dateRange);
const { chartColors } = useChartColors();

const dateRangeOptions = [
    { value: 'today', label: 'Today' },
    { value: '7d', label: 'Last 7 Days' },
    { value: '30d', label: 'Last 30 Days' },
    { value: '90d', label: 'Last 90 Days' },
    { value: 'year', label: 'This Year' },
];

/**
 * menu bar chart data 
 */
const menuBarData = computed(() => {
    return [
        { label: 'Total', value: props.metrics.total_menus },
        { label: 'Active', value: props.metrics.active_menus },
        { label: 'Inactive', value: props.metrics.inactive_menus },
        { label: 'Categories', value: props.metrics.total_categories },
        { label: 'Types', value: props.metrics.total_types },
    ];
});

const menuChartConfig = computed(() => ({
    value: { label: 'Count', color: chartColors.value.chart1 },
}));

watch(selectedDateRange, (newValue) => {
    emit('dateRangeChange', newValue);
});

const handleRefresh = () => {
    emit('refresh');
};

const formatNumber = (num: number) => {
    return new Intl.NumberFormat().format(num);
};
</script>

<template>
    <div class="space-y-6">
        <!-- Header with Date Filter -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold tracking-tight">Menu Performance Metrics</h2>
                <p class="text-sm text-muted-foreground">Track menus and categories overview</p>
            </div>
            <div class="flex items-center gap-2">
                <Select v-model="selectedDateRange">
                    <SelectTrigger class="w-[160px]">
                        <Calendar class="mr-2 h-4 w-4" />
                        <SelectValue placeholder="Select period" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="option in dateRangeOptions"
                            :key="option.value"
                            :value="option.value"
                        >
                            {{ option.label }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <Button variant="outline" size="icon" @click="handleRefresh" :disabled="loading">
                    <RefreshCw class="h-4 w-4" :class="{ 'animate-spin': loading }" />
                </Button>
            </div>
        </div>

        <!-- Key Metrics Grid -->
        <div class="grid gap-4 md:grid-cols-5">
            <!-- Total Menus -->
            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium">Total Menus</CardTitle>
                    <UtensilsCrossed class="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold">{{ formatNumber(metrics.total_menus) }}</div>
                    <p class="text-xs text-muted-foreground">All menus in system</p>
                </CardContent>
            </Card>

            <!-- Active Menus -->
            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium">Active Menus</CardTitle>
                    <CheckCircle class="h-4 w-4 text-green-500" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold text-green-600">{{ formatNumber(metrics.active_menus) }}</div>
                    <p class="text-xs text-muted-foreground">
                        {{ ((metrics.active_menus / metrics.total_menus) * 100).toFixed(1) }}% of total
                    </p>
                </CardContent>
            </Card>

            <!-- Inactive Menus -->
            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium">Inactive Menus</CardTitle>
                    <XCircle class="h-4 w-4 text-yellow-500" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold text-yellow-600">{{ formatNumber(metrics.inactive_menus) }}</div>
                    <p class="text-xs text-muted-foreground">
                        {{ ((metrics.inactive_menus / metrics.total_menus) * 100).toFixed(1) }}% of total
                    </p>
                </CardContent>
            </Card>

            <!-- Categories -->
            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium">Categories</CardTitle>
                    <FolderTree class="h-4 w-4 text-blue-500" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold text-blue-600">{{ formatNumber(metrics.total_categories) }}</div>
                    <p class="text-xs text-muted-foreground">Menu categories</p>
                </CardContent>
            </Card>

            <!-- Menu Types -->
            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium">Menu Types</CardTitle>
                    <Layers class="h-4 w-4 text-purple-500" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold text-purple-600">{{ formatNumber(metrics.total_types) }}</div>
                    <p class="text-xs text-muted-foreground">Different menu types</p>
                </CardContent>
            </Card>
        </div>

        <!-- Menu Bar Chart -->
        <Card>
            <CardHeader>
                <CardTitle>Menu Statistics</CardTitle>
                <CardDescription>Overview of menu data</CardDescription>
            </CardHeader>
            <CardContent>
                <ChartContainer :config="menuChartConfig" class="h-[300px]">
                    <VisXYContainer :data="menuBarData">
                        <VisStackedBar
                            :x="(_: any, i: number) => i"
                            :y="(d: any) => d.value"
                            :color="chartColors.chart1"
                            :barPadding="0.3"
                            :roundedCorners="4"
                        />
                        <VisAxis
                            type="x"
                            :tickFormat="(i: number) => menuBarData[i]?.label || ''"
                        />
                        <VisAxis type="y" />
                    </VisXYContainer>
                </ChartContainer>
            </CardContent>
        </Card>
    </div>
</template>