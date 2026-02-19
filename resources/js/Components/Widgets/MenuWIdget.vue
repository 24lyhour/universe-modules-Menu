<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { ChartContainer } from '@/components/ui/chart';
import { Progress } from '@/components/ui/progress';
import { VisXYContainer, VisArea, VisLine, VisAxis } from '@unovis/vue';
import {
    UtensilsCrossed,
    CheckCircle,
    XCircle,
    Layers,
    FolderTree,
    RefreshCw,
    Calendar,
    Package,
    PackageCheck,
    PackageX,
    FolderOpen,
    FolderClosed,
    TrendingUp,
} from 'lucide-vue-next';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Button } from '@/components/ui/button';
import { useChartColors } from '@/composables/useChartColors';

interface TopCategory {
    id: number;
    name: string;
    status: boolean;
    products_count: number;
}

interface GrowthTrendPoint {
    label: string;
    categories: number;
    products: number;
}

export interface MenuMetrics {
    // Menu stats
    total_menus: number;
    active_menus: number;
    inactive_menus: number;
    total_types: number;
    // Category stats
    total_categories: number;
    active_categories: number;
    inactive_categories: number;
    categories_with_products: number;
    categories_without_products: number;
    // Product in categories stats
    total_products_in_categories: number;
    available_products: number;
    unavailable_products: number;
    // Top categories
    top_categories: TopCategory[];
    // Growth trend
    growth_trend: GrowthTrendPoint[];
}

export interface MenuWidgetProps {
    metrics: MenuMetrics;
    dateRange?: string;
    loading?: boolean;
    showStats?: boolean;
    showDistribution?: boolean;
    showBar?: boolean;
    showArea?: boolean;
}

const props = withDefaults(defineProps<MenuWidgetProps>(), {
    dateRange: '30d',
    loading: false,
    showStats: true,
    showDistribution: true,
    showBar: true,
    showArea: true,
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

// Safe metrics accessor with defaults
const safeMetrics = computed(() => ({
    total_menus: props.metrics?.total_menus ?? 0,
    active_menus: props.metrics?.active_menus ?? 0,
    inactive_menus: props.metrics?.inactive_menus ?? 0,
    total_types: props.metrics?.total_types ?? 0,
    total_categories: props.metrics?.total_categories ?? 0,
    active_categories: props.metrics?.active_categories ?? 0,
    inactive_categories: props.metrics?.inactive_categories ?? 0,
    categories_with_products: props.metrics?.categories_with_products ?? 0,
    categories_without_products: props.metrics?.categories_without_products ?? 0,
    total_products_in_categories: props.metrics?.total_products_in_categories ?? 0,
    available_products: props.metrics?.available_products ?? 0,
    unavailable_products: props.metrics?.unavailable_products ?? 0,
    top_categories: props.metrics?.top_categories ?? [],
    growth_trend: props.metrics?.growth_trend ?? [],
}));

// Top categories data
const topCategories = computed(() => safeMetrics.value.top_categories);

// Growth trend data
const growthTrend = computed(() => safeMetrics.value.growth_trend);
const hasGrowthData = computed(() => growthTrend.value.length > 0);

// Chart config
const chartConfig = computed(() => ({
    categories: { label: 'Categories', color: chartColors.value.chart1 },
    products: { label: 'Products', color: chartColors.value.chart2 },
}));

watch(selectedDateRange, (newValue) => {
    emit('dateRangeChange', newValue);
});

const handleRefresh = () => {
    emit('refresh');
};

const formatNumber = (num: number | undefined | null) => {
    return new Intl.NumberFormat().format(num ?? 0);
};

const getPercentage = (value: number | undefined | null, total: number | undefined | null) => {
    const v = value ?? 0;
    const t = total ?? 0;
    if (t === 0) return '0.0';
    return ((v / t) * 100).toFixed(1);
};
</script>

<template>
    <div class="space-y-6">
        <!-- Header with Date Filter -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold tracking-tight">Menu Performance Metrics</h2>
                <p class="text-sm text-muted-foreground">Track menus, categories and products overview</p>
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

        <!-- Menu Stats Row -->
        <div v-if="showStats" class="grid gap-4 md:grid-cols-4">
            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium">Total Menus</CardTitle>
                    <UtensilsCrossed class="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold">{{ formatNumber(safeMetrics.total_menus) }}</div>
                    <p class="text-xs text-muted-foreground">All menus in system</p>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium">Active Menus</CardTitle>
                    <CheckCircle class="h-4 w-4 text-green-500" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold text-green-600">{{ formatNumber(safeMetrics.active_menus) }}</div>
                    <p class="text-xs text-muted-foreground">
                        {{ getPercentage(safeMetrics.active_menus, safeMetrics.total_menus) }}% of total
                    </p>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium">Inactive Menus</CardTitle>
                    <XCircle class="h-4 w-4 text-yellow-500" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold text-yellow-600">{{ formatNumber(safeMetrics.inactive_menus) }}</div>
                    <p class="text-xs text-muted-foreground">
                        {{ getPercentage(safeMetrics.inactive_menus, safeMetrics.total_menus) }}% of total
                    </p>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium">Menu Types</CardTitle>
                    <Layers class="h-4 w-4 text-purple-500" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold text-purple-600">{{ formatNumber(safeMetrics.total_types) }}</div>
                    <p class="text-xs text-muted-foreground">Different menu types</p>
                </CardContent>
            </Card>
        </div>

        <!-- Category Stats Row -->
        <div v-if="showStats" class="grid gap-4 md:grid-cols-4">
            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium">Total Categories</CardTitle>
                    <FolderTree class="h-4 w-4 text-blue-500" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold text-blue-600">{{ formatNumber(safeMetrics.total_categories) }}</div>
                    <p class="text-xs text-muted-foreground">Menu categories</p>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium">Active Categories</CardTitle>
                    <FolderOpen class="h-4 w-4 text-green-500" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold text-green-600">{{ formatNumber(safeMetrics.active_categories) }}</div>
                    <p class="text-xs text-muted-foreground">
                        {{ getPercentage(safeMetrics.active_categories, safeMetrics.total_categories) }}% of total
                    </p>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium">With Products</CardTitle>
                    <PackageCheck class="h-4 w-4 text-emerald-500" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold text-emerald-600">{{ formatNumber(safeMetrics.categories_with_products) }}</div>
                    <p class="text-xs text-muted-foreground">
                        {{ getPercentage(safeMetrics.categories_with_products, safeMetrics.total_categories) }}% have products
                    </p>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium">Without Products</CardTitle>
                    <FolderClosed class="h-4 w-4 text-orange-500" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold text-orange-600">{{ formatNumber(safeMetrics.categories_without_products) }}</div>
                    <p class="text-xs text-muted-foreground">
                        {{ getPercentage(safeMetrics.categories_without_products, safeMetrics.total_categories) }}% empty
                    </p>
                </CardContent>
            </Card>
        </div>

        <!-- Products in Categories Stats Row -->
        <div v-if="showStats" class="grid gap-4 md:grid-cols-3">
            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium">Products in Categories</CardTitle>
                    <Package class="h-4 w-4 text-indigo-500" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold text-indigo-600">{{ formatNumber(safeMetrics.total_products_in_categories) }}</div>
                    <p class="text-xs text-muted-foreground">Total product assignments</p>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium">Available Products</CardTitle>
                    <PackageCheck class="h-4 w-4 text-green-500" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold text-green-600">{{ formatNumber(safeMetrics.available_products) }}</div>
                    <p class="text-xs text-muted-foreground">
                        {{ getPercentage(safeMetrics.available_products, safeMetrics.total_products_in_categories) }}% available
                    </p>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium">Unavailable Products</CardTitle>
                    <PackageX class="h-4 w-4 text-red-500" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold text-red-600">{{ formatNumber(safeMetrics.unavailable_products) }}</div>
                    <p class="text-xs text-muted-foreground">
                        {{ getPercentage(safeMetrics.unavailable_products, safeMetrics.total_products_in_categories) }}% unavailable
                    </p>
                </CardContent>
            </Card>
        </div>

        <!-- Charts Row -->
        <div v-if="showDistribution || showBar" class="grid gap-4 md:grid-cols-2">
            <!-- Category Distribution -->
            <Card v-if="showDistribution">
                <CardHeader>
                    <CardTitle>Category Distribution</CardTitle>
                    <CardDescription>Categories with vs without products</CardDescription>
                </CardHeader>
                <CardContent>
                    <div v-if="safeMetrics.total_categories > 0" class="space-y-6">
                        <!-- With Products -->
                        <div class="space-y-2">
                            <div class="flex items-center justify-between text-sm">
                                <div class="flex items-center gap-2">
                                    <div class="h-3 w-3 rounded-full bg-emerald-500"></div>
                                    <span class="font-medium">With Products</span>
                                </div>
                                <span class="text-muted-foreground">
                                    {{ safeMetrics.categories_with_products }} ({{ getPercentage(safeMetrics.categories_with_products, safeMetrics.total_categories) }}%)
                                </span>
                            </div>
                            <Progress
                                :model-value="(safeMetrics.categories_with_products / safeMetrics.total_categories) * 100"
                                class="h-3 bg-emerald-100"
                            />
                        </div>
                        <!-- Without Products -->
                        <div class="space-y-2">
                            <div class="flex items-center justify-between text-sm">
                                <div class="flex items-center gap-2">
                                    <div class="h-3 w-3 rounded-full bg-orange-500"></div>
                                    <span class="font-medium">Without Products</span>
                                </div>
                                <span class="text-muted-foreground">
                                    {{ safeMetrics.categories_without_products }} ({{ getPercentage(safeMetrics.categories_without_products, safeMetrics.total_categories) }}%)
                                </span>
                            </div>
                            <Progress
                                :model-value="(safeMetrics.categories_without_products / safeMetrics.total_categories) * 100"
                                class="h-3 bg-orange-100"
                            />
                        </div>
                        <!-- Product Availability -->
                        <div class="pt-4 border-t space-y-4">
                            <h4 class="text-sm font-medium">Product Availability in Categories</h4>
                            <div class="space-y-2">
                                <div class="flex items-center justify-between text-sm">
                                    <div class="flex items-center gap-2">
                                        <div class="h-3 w-3 rounded-full bg-green-500"></div>
                                        <span>Available</span>
                                    </div>
                                    <span class="text-muted-foreground">
                                        {{ safeMetrics.available_products }} ({{ getPercentage(safeMetrics.available_products, safeMetrics.total_products_in_categories) }}%)
                                    </span>
                                </div>
                                <Progress
                                    :model-value="safeMetrics.total_products_in_categories > 0 ? (safeMetrics.available_products / safeMetrics.total_products_in_categories) * 100 : 0"
                                    class="h-2"
                                />
                            </div>
                            <div class="space-y-2">
                                <div class="flex items-center justify-between text-sm">
                                    <div class="flex items-center gap-2">
                                        <div class="h-3 w-3 rounded-full bg-red-500"></div>
                                        <span>Unavailable</span>
                                    </div>
                                    <span class="text-muted-foreground">
                                        {{ safeMetrics.unavailable_products }} ({{ getPercentage(safeMetrics.unavailable_products, safeMetrics.total_products_in_categories) }}%)
                                    </span>
                                </div>
                                <Progress
                                    :model-value="safeMetrics.total_products_in_categories > 0 ? (safeMetrics.unavailable_products / safeMetrics.total_products_in_categories) * 100 : 0"
                                    class="h-2"
                                />
                            </div>
                        </div>
                    </div>
                    <div v-else class="flex items-center justify-center h-[200px] text-muted-foreground">
                        No category data available
                    </div>
                </CardContent>
            </Card>

            <!-- Top Categories Bar Chart -->
            <Card v-if="showBar">
                <CardHeader>
                    <CardTitle>Top Categories by Products</CardTitle>
                    <CardDescription>Categories with most products assigned</CardDescription>
                </CardHeader>
                <CardContent>
                    <div v-if="topCategories.length > 0" class="space-y-4">
                        <div
                            v-for="cat in topCategories"
                            :key="cat.id"
                            class="space-y-2"
                        >
                            <div class="flex items-center justify-between text-sm">
                                <span class="font-medium truncate max-w-[180px]">{{ cat.name }}</span>
                                <span class="text-muted-foreground">{{ cat.products_count }} products</span>
                            </div>
                            <Progress
                                :model-value="(cat.products_count / (topCategories[0]?.products_count || 1)) * 100"
                                class="h-2"
                            />
                        </div>
                    </div>
                    <div v-else class="flex items-center justify-center h-[200px] text-muted-foreground">
                        No categories with products yet
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Growth Trend Chart -->
        <Card v-if="showArea && hasGrowthData">
            <CardHeader>
                <CardTitle class="flex items-center gap-2">
                    <TrendingUp class="h-5 w-5" />
                    Growth Trend
                </CardTitle>
                <CardDescription>Categories and products added over time</CardDescription>
            </CardHeader>
            <CardContent>
                <ChartContainer :config="chartConfig" class="h-[280px]">
                    <VisXYContainer :data="growthTrend" :margin="{ top: 10, bottom: 30, left: 40, right: 10 }">
                        <VisArea
                            :x="(_: GrowthTrendPoint, i: number) => i"
                            :y="(d: GrowthTrendPoint) => d.categories"
                            :color="chartColors.chart1"
                            :opacity="0.3"
                        />
                        <VisLine
                            :x="(_: GrowthTrendPoint, i: number) => i"
                            :y="(d: GrowthTrendPoint) => d.categories"
                            :color="chartColors.chart1"
                            :line-width="2"
                        />
                        <VisArea
                            :x="(_: GrowthTrendPoint, i: number) => i"
                            :y="(d: GrowthTrendPoint) => d.products"
                            :color="chartColors.chart2"
                            :opacity="0.3"
                        />
                        <VisLine
                            :x="(_: GrowthTrendPoint, i: number) => i"
                            :y="(d: GrowthTrendPoint) => d.products"
                            :color="chartColors.chart2"
                            :line-width="2"
                        />
                        <VisAxis
                            type="x"
                            :tick-format="(i: number) => growthTrend[i]?.label || ''"
                        />
                        <VisAxis type="y" />
                    </VisXYContainer>
                </ChartContainer>
                <div class="mt-4 flex justify-center gap-6">
                    <div class="flex items-center gap-2">
                        <div class="h-3 w-3 rounded-full" :style="{ backgroundColor: chartColors.chart1 }"></div>
                        <span class="text-sm">Categories Created</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="h-3 w-3 rounded-full" :style="{ backgroundColor: chartColors.chart2 }"></div>
                        <span class="text-sm">Products Assigned</span>
                    </div>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
