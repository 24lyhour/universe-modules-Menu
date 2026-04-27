<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useModal } from 'momentum-modal';
import { ModalForm } from '@/components/shared';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Separator } from '@/components/ui/separator';
import { Clock, Calendar, CalendarDays, CalendarRange } from 'lucide-vue-next';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import type { Menu } from '@menu/types';

interface Props {
    menu: Menu;
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

const DAYS_OF_WEEK = [
    { value: 'monday', label: 'Mon' },
    { value: 'tuesday', label: 'Tue' },
    { value: 'wednesday', label: 'Wed' },
    { value: 'thursday', label: 'Thu' },
    { value: 'friday', label: 'Fri' },
    { value: 'saturday', label: 'Sat' },
    { value: 'sunday', label: 'Sun' },
];

const SCHEDULE_MODES = [
    { value: 'always', label: 'Always Open', description: 'Available 24/7', icon: Clock },
    { value: 'daily', label: 'Daily Schedule', description: 'Same hours every day', icon: Calendar },
    { value: 'weekly', label: 'Weekly Schedule', description: 'Different hours per day', icon: CalendarDays },
    { value: 'date_range', label: 'Date Range', description: 'Temporary schedule', icon: CalendarRange },
];

// Parse initial days from menu
const parseInitialDays = (): string[] => {
    if (!props.menu.schedule_days) return [];
    try {
        const days = JSON.parse(props.menu.schedule_days);
        return Array.isArray(days) ? days : [];
    } catch {
        return props.menu.schedule_days.split(',');
    }
};

const selectedDays = ref<string[]>(parseInitialDays());

const form = useForm({
    schedule_mode: props.menu.schedule_mode || '',
    schedule_days: props.menu.schedule_days || '',
    schedule_start_time: props.menu.schedule_start_time || '',
    schedule_end_time: props.menu.schedule_end_time || '',
    schedule_start_date: props.menu.schedule_start_date || '',
    schedule_end_date: props.menu.schedule_end_date || '',
    schedule_status: props.menu.schedule_status || false,
});

const isScheduleEnabled = computed({
    get: () => form.schedule_status === true,
    set: (value: boolean) => {
        form.schedule_status = value;
    },
});

const toggleDay = (day: string) => {
    const index = selectedDays.value.indexOf(day);
    if (index > -1) {
        selectedDays.value.splice(index, 1);
    } else {
        selectedDays.value.push(day);
    }
};

const isDaySelected = (day: string) => selectedDays.value.includes(day);

const selectedModeInfo = computed(() => {
    return SCHEDULE_MODES.find(m => m.value === form.schedule_mode);
});

const handleSubmit = () => {
    // Only send fields relevant to the chosen mode. The backend mirrors
    // this and nulls anything else, but trimming the payload here keeps
    // validation errors meaningful.
    const mode = form.schedule_mode;
    form.schedule_days = mode === 'weekly' && selectedDays.value.length
        ? JSON.stringify(selectedDays.value)
        : '';
    if (mode === 'always') {
        form.schedule_start_time = '';
        form.schedule_end_time = '';
        form.schedule_start_date = '';
        form.schedule_end_date = '';
    } else if (mode !== 'date_range') {
        form.schedule_start_date = '';
        form.schedule_end_date = '';
    }

    form.put(`/dashboard/menus/${props.menu.uuid}/schedule`, {
        preserveScroll: true,
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
        title="Manage Schedule"
        description="Configure the menu's availability schedule"
        mode="edit"
        size="lg"
        submit-text="Save Schedule"
        :loading="form.processing"
        @submit="handleSubmit"
        @cancel="handleCancel"
    >
        <div class="space-y-6">
            <!-- Enable Schedule -->
            <div class="flex items-center justify-between rounded-lg border p-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full" :class="isScheduleEnabled ? 'bg-primary/10' : 'bg-muted'">
                        <Clock class="h-5 w-5" :class="isScheduleEnabled ? 'text-primary' : 'text-muted-foreground'" />
                    </div>
                    <div>
                        <Label class="text-base font-medium">Enable Schedule</Label>
                        <p class="text-sm text-muted-foreground">
                            {{ isScheduleEnabled ? 'Schedule is active' : 'Turn on to set availability hours' }}
                        </p>
                    </div>
                </div>
                <Switch v-model="isScheduleEnabled" />
            </div>

            <Separator />

            <!-- Schedule Mode -->
            <div class="space-y-3">
                <Label class="text-base font-medium">Schedule Mode</Label>
                <Select v-model="form.schedule_mode">
                    <SelectTrigger class="w-full h-12">
                        <SelectValue placeholder="Select schedule mode">
                            <div v-if="selectedModeInfo" class="flex items-center gap-2">
                                <component :is="selectedModeInfo.icon" class="h-4 w-4" />
                                <span>{{ selectedModeInfo.label }}</span>
                            </div>
                        </SelectValue>
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="mode in SCHEDULE_MODES"
                            :key="mode.value"
                            :value="mode.value"
                        >
                            <div class="flex items-center gap-3">
                                <component :is="mode.icon" class="h-4 w-4 text-muted-foreground" />
                                <div>
                                    <div class="font-medium">{{ mode.label }}</div>
                                    <div class="text-xs text-muted-foreground">{{ mode.description }}</div>
                                </div>
                            </div>
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <!-- Days Selection (for weekly mode) -->
            <div v-if="form.schedule_mode === 'weekly'" class="space-y-3">
                <Label class="text-base font-medium">Available Days</Label>
                <p class="text-sm text-muted-foreground">Select the days this menu is available</p>
                <div class="grid grid-cols-7 gap-2">
                    <Button
                        v-for="day in DAYS_OF_WEEK"
                        :key="day.value"
                        type="button"
                        size="sm"
                        class="h-10"
                        :variant="isDaySelected(day.value) ? 'default' : 'outline'"
                        @click="toggleDay(day.value)"
                    >
                        {{ day.label }}
                    </Button>
                </div>
            </div>

            <!-- Time Range -->
            <div v-if="form.schedule_mode && form.schedule_mode !== 'always'" class="space-y-3">
                <Label class="text-base font-medium">Availability Hours</Label>
                <p class="text-sm text-muted-foreground">Set start and end times</p>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="space-y-2">
                        <Label for="schedule_start_time" class="text-sm">Start Time</Label>
                        <Input
                            id="schedule_start_time"
                            v-model="form.schedule_start_time"
                            type="time"
                            class="h-11"
                        />
                    </div>
                    <div class="space-y-2">
                        <Label for="schedule_end_time" class="text-sm">End Time</Label>
                        <Input
                            id="schedule_end_time"
                            v-model="form.schedule_end_time"
                            type="time"
                            class="h-11"
                        />
                    </div>
                </div>
            </div>

            <!-- Date Range (for date_range mode) -->
            <div v-if="form.schedule_mode === 'date_range'" class="space-y-3">
                <Label class="text-base font-medium">Date Range</Label>
                <p class="text-sm text-muted-foreground">Set the start and end dates for this schedule</p>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="space-y-2">
                        <Label for="schedule_start_date" class="text-sm">Start Date</Label>
                        <Input
                            id="schedule_start_date"
                            v-model="form.schedule_start_date"
                            type="date"
                            class="h-11"
                        />
                    </div>
                    <div class="space-y-2">
                        <Label for="schedule_end_date" class="text-sm">End Date</Label>
                        <Input
                            id="schedule_end_date"
                            v-model="form.schedule_end_date"
                            type="date"
                            class="h-11"
                        />
                    </div>
                </div>
            </div>
        </div>
    </ModalForm>
</template>
