<script setup lang="ts">
import { ModalForm } from '@/components/shared';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Separator } from '@/components/ui/separator';
import { Switch } from '@/components/ui/switch';
import { useForm } from '@inertiajs/vue3';
import { Check, GripVertical, Plus, Search, Trash2, X } from 'lucide-vue-next';
import { useModal } from 'momentum-modal';
import { computed, ref } from 'vue';

interface Product {
    id: number;
    name: string;
    sku: string | null;
    price: number;
    image_url: string | null;
}

interface AssignedProduct extends Product {
    pivot: {
        price_override: number | null;
        sort_order: number;
        is_available: boolean;
    };
}

interface Props {
    category: {
        id: number;
        name: string;
    };
    allProducts: Product[];
    assignedProducts: AssignedProduct[];
    returnTo?: string;
    menuId?: string;
}

const props = defineProps<Props>();

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

// Local state for managing products
const selectedProducts = ref<AssignedProduct[]>([...props.assignedProducts]);
const searchQuery = ref('');

// Form for submission
const form = useForm({
    products: props.assignedProducts.map((p) => ({
        id: p.id,
        price_override: p.pivot.price_override,
        sort_order: p.pivot.sort_order,
        is_available: p.pivot.is_available,
    })),
});

// Available products (not yet assigned)
const availableProducts = computed(() => {
    const assignedIds = selectedProducts.value.map((p) => p.id);
    return props.allProducts.filter((p) => !assignedIds.includes(p.id));
});

// Filtered available products based on search
const filteredAvailableProducts = computed(() => {
    if (!searchQuery.value) return availableProducts.value;
    const query = searchQuery.value.toLowerCase();
    return availableProducts.value.filter(
        (p) =>
            p.name.toLowerCase().includes(query) ||
            (p.sku && p.sku.toLowerCase().includes(query))
    );
});

// Check if there are changes
const hasChanges = computed(() => {
    const originalIds = props.assignedProducts.map((p) => p.id).sort();
    const currentIds = selectedProducts.value.map((p) => p.id).sort();

    if (originalIds.length !== currentIds.length) return true;
    if (originalIds.some((id, i) => id !== currentIds[i])) return true;

    // Check for pivot changes
    for (const product of selectedProducts.value) {
        const original = props.assignedProducts.find((p) => p.id === product.id);
        if (!original) return true;
        if (
            product.pivot.price_override !== original.pivot.price_override ||
            product.pivot.sort_order !== original.pivot.sort_order ||
            product.pivot.is_available !== original.pivot.is_available
        ) {
            return true;
        }
    }

    return false;
});

// Add product to selected
const addProduct = (product: Product) => {
    selectedProducts.value.push({
        ...product,
        pivot: {
            price_override: null,
            sort_order: selectedProducts.value.length,
            is_available: true,
        },
    });
    updateFormProducts();
};

// Remove product from selected
const removeProduct = (productId: number) => {
    const index = selectedProducts.value.findIndex((p) => p.id === productId);
    if (index !== -1) {
        selectedProducts.value.splice(index, 1);
        // Update sort orders
        selectedProducts.value.forEach((p, i) => {
            p.pivot.sort_order = i;
        });
        updateFormProducts();
    }
};

// Update pivot data
const updatePivot = (
    productId: number,
    field: 'price_override' | 'sort_order' | 'is_available',
    value: number | boolean | null
) => {
    const product = selectedProducts.value.find((p) => p.id === productId);
    if (product) {
        (product.pivot as Record<string, unknown>)[field] = value;
        updateFormProducts();
    }
};

// Update form products array
const updateFormProducts = () => {
    form.products = selectedProducts.value.map((p) => ({
        id: p.id,
        price_override: p.pivot.price_override,
        sort_order: p.pivot.sort_order,
        is_available: p.pivot.is_available,
    }));
};

// Format currency
const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(value);
};

const handleSubmit = () => {
    // Build query string for return context
    const params = new URLSearchParams();
    if (props.returnTo) params.append('return_to', props.returnTo);
    if (props.menuId) params.append('menu_id', String(props.menuId));
    const queryString = params.toString() ? `?${params.toString()}` : '';

    form.post(`/dashboard/categories/${props.category.id}/products/sync${queryString}`, {
        onSuccess: () => {
            close();
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
        :title="`Manage Products - ${category.name}`"
        :description="`${selectedProducts.length} product(s) assigned to this category`"
        mode="edit"
        size="xl"
        :submit-text="hasChanges ? 'Save Changes' : 'No Changes'"
        :loading="form.processing"
        :disabled="!hasChanges"
        @submit="handleSubmit"
        @cancel="handleCancel"
    >
        <div class="grid gap-6 lg:grid-cols-2">
            <!-- Assigned Products -->
            <div class="space-y-4">
                <div class="flex items-center gap-2">
                    <Check class="h-4 w-4 text-green-600" />
                    <h4 class="text-sm font-semibold">
                        Assigned Products ({{ selectedProducts.length }})
                    </h4>
                </div>

                <ScrollArea class="h-[350px] pr-4">
                    <div v-if="selectedProducts.length === 0" class="text-center py-8 text-muted-foreground">
                        No products assigned yet. Add products from the right panel.
                    </div>

                    <div v-else class="space-y-3">
                        <div
                            v-for="product in selectedProducts"
                            :key="product.id"
                            class="p-3 rounded-lg border border-green-200 bg-green-50 dark:border-green-900 dark:bg-green-950/30"
                        >
                            <div class="flex items-start gap-3">
                                <div class="flex items-center pt-1 text-muted-foreground cursor-grab">
                                    <GripVertical class="h-4 w-4" />
                                </div>

                                <div class="flex-1 space-y-3">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <span class="font-medium text-sm">{{ product.name }}</span>
                                            <div class="flex items-center gap-2 mt-1">
                                                <Badge variant="outline" class="text-xs">
                                                    {{ product.sku || 'No SKU' }}
                                                </Badge>
                                                <span class="text-xs text-muted-foreground">
                                                    Base: {{ formatCurrency(product.price) }}
                                                </span>
                                            </div>
                                        </div>
                                        <Button
                                            type="button"
                                            variant="ghost"
                                            size="icon"
                                            class="h-8 w-8 text-red-500 hover:text-red-600 hover:bg-red-100"
                                            @click="removeProduct(product.id)"
                                        >
                                            <Trash2 class="h-4 w-4" />
                                        </Button>
                                    </div>

                                    <div class="grid grid-cols-2 gap-3">
                                        <div class="space-y-1">
                                            <Label class="text-xs">Price Override</Label>
                                            <Input
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                placeholder="Use base price"
                                                :model-value="product.pivot.price_override ?? ''"
                                                class="h-8 text-sm"
                                                @update:model-value="
                                                    updatePivot(
                                                        product.id,
                                                        'price_override',
                                                        $event ? Number($event) : null
                                                    )
                                                "
                                            />
                                        </div>
                                        <div class="flex items-end justify-between gap-2">
                                            <div class="space-y-1">
                                                <Label class="text-xs">Available</Label>
                                                <Switch
                                                    :checked="product.pivot.is_available"
                                                    @update:checked="updatePivot(product.id, 'is_available', $event)"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </ScrollArea>
            </div>

            <!-- Available Products -->
            <div class="space-y-4">
                <div class="flex items-center gap-2">
                    <Plus class="h-4 w-4 text-muted-foreground" />
                    <h4 class="text-sm font-semibold text-muted-foreground">
                        Available Products ({{ availableProducts.length }})
                    </h4>
                </div>

                <!-- Search -->
                <div class="relative">
                    <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input
                        v-model="searchQuery"
                        placeholder="Search products..."
                        class="pl-9"
                    />
                </div>

                <ScrollArea class="h-[300px] pr-4">
                    <div v-if="filteredAvailableProducts.length === 0" class="text-center py-8 text-muted-foreground">
                        {{ searchQuery ? 'No products found.' : 'All products are assigned.' }}
                    </div>

                    <div v-else class="space-y-2">
                        <div
                            v-for="product in filteredAvailableProducts"
                            :key="product.id"
                            class="flex items-center justify-between p-3 rounded-lg border hover:bg-muted/50 transition-colors cursor-pointer"
                            @click="addProduct(product)"
                        >
                            <div class="flex-1">
                                <span class="text-sm font-medium">{{ product.name }}</span>
                                <div class="flex items-center gap-2 mt-1">
                                    <Badge variant="outline" class="text-xs">
                                        {{ product.sku || 'No SKU' }}
                                    </Badge>
                                    <span class="text-xs text-muted-foreground">
                                        {{ formatCurrency(product.price) }}
                                    </span>
                                </div>
                            </div>
                            <Button
                                type="button"
                                variant="ghost"
                                size="icon"
                                class="h-8 w-8 text-green-600 hover:text-green-700 hover:bg-green-100"
                                @click.stop="addProduct(product)"
                            >
                                <Plus class="h-4 w-4" />
                            </Button>
                        </div>
                    </div>
                </ScrollArea>
            </div>
        </div>
    </ModalForm>
</template>
