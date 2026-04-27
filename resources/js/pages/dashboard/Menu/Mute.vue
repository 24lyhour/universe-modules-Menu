<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useModal } from 'momentum-modal';
import { ModalForm } from '@/components/shared';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { Megaphone, BellOff, Clock, User as UserIcon } from 'lucide-vue-next';
import type { Menu, MutePreset } from '@menu/types';
import TiptapEditor from '@/components/TiptapEditor.vue';

interface Props {
    menu: Menu;
    presets: MutePreset[];
}

const props = withDefaults(defineProps<Props>(), {
    presets: () => [
        { key: '1h', label: '1 hour' },
        { key: '2h', label: '2 hours' },
        { key: '4h', label: '4 hours' },
        { key: 'today', label: 'Rest of day' },
        { key: '1d', label: '1 day' },
        { key: 'forever', label: 'Until I unmute' },
    ],
});

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

// ─── Live mute state (respects auto-expiry) ─────────────────────────
const now = ref(Date.now());
let tickerId: ReturnType<typeof setInterval> | null = null;
onMounted(() => { tickerId = setInterval(() => { now.value = Date.now(); }, 1000); });
onUnmounted(() => { if (tickerId) clearInterval(tickerId); });

const isCurrentlyMuted = computed(() => {
    if (!props.menu.is_muted) return false;
    if (!props.menu.muted_until) return true;
    return new Date(props.menu.muted_until).getTime() > now.value;
});

const remaining = computed(() => {
    if (!props.menu.muted_until) return null;
    const diff = new Date(props.menu.muted_until).getTime() - now.value;
    if (diff <= 0) return null;

    const totalMin = Math.floor(diff / 60000);
    const days = Math.floor(totalMin / 1440);
    const hours = Math.floor((totalMin % 1440) / 60);
    const minutes = totalMin % 60;
    if (days > 0) return `${days}d ${hours}h ${minutes}m`;
    if (hours > 0) return `${hours}h ${minutes}m`;
    return `${minutes}m`;
});

// ─── Mute form ──────────────────────────────────────────────────────
const muteForm = useForm({
    preset: '4h',
    muted_until: '',
    reason: '',
});

// Sensible default for the datetime picker: 4 hours from now, in local format.
const minDateTime = computed(() => {
    const d = new Date(Date.now() + 60_000);
    return toLocalInput(d);
});
function toLocalInput(d: Date): string {
    const pad = (n: number) => n.toString().padStart(2, '0');
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
}

const submitMute = () => {
    muteForm.post(`/dashboard/menus/${props.menu.uuid}/mute`, {
        preserveScroll: true,
        onSuccess: () => {
            close();
            redirect();
        },
    });
};

// ─── Unmute ─────────────────────────────────────────────────────────
const unmuting = ref(false);
const unmuteForm = useForm({});

const submitUnmute = () => {
    unmuting.value = true;
    unmuteForm.delete(`/dashboard/menus/${props.menu.uuid}/mute`, {
        preserveScroll: true,
        onFinish: () => { unmuting.value = false; },
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

const formatDate = (iso: string | null) => {
    if (!iso) return '—';
    return new Date(iso).toLocaleString('en-US', {
        month: 'short', day: 'numeric',
        hour: '2-digit', minute: '2-digit',
    });
};
</script>

<template>
    <ModalForm
        v-model:open="isOpen"
        :title="isCurrentlyMuted ? 'Unmute item' : 'Mute item'"
        :description="isCurrentlyMuted
            ? 'Lift the temporary hide and make this available again.'
            : 'Temporarily hide this item without changing its status.'"
        mode="edit"
        size="md"
        :submit-text="isCurrentlyMuted ? 'Unmute now' : 'Mute item'"
        :loading="muteForm.processing || unmuting"
        @submit="isCurrentlyMuted ? submitUnmute() : submitMute()"
        @cancel="handleCancel"
    >
        <div class="space-y-5">
            <!-- Item header -->
            <div class="flex items-center gap-3 rounded-lg border p-3">
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-full"
                    :class="isCurrentlyMuted ? 'bg-amber-100 dark:bg-amber-950/40' : 'bg-muted'"
                >
                    <BellOff v-if="isCurrentlyMuted" class="h-5 w-5 text-amber-600" />
                    <Megaphone v-else class="h-5 w-5 text-muted-foreground" />
                </div>
                <div class="min-w-0 flex-1">
                    <p class="truncate font-medium">{{ menu.name }}</p>
                    <p class="text-xs text-muted-foreground">
                        {{ isCurrentlyMuted ? 'Currently muted' : 'Currently available' }}
                    </p>
                </div>
            </div>

            <!-- Currently muted: show details + unmute -->
            <template v-if="isCurrentlyMuted">
                <div class="space-y-3 rounded-lg border bg-amber-50/50 p-4 dark:bg-amber-950/20">
                    <div class="flex items-center gap-2">
                        <Clock class="h-4 w-4 text-amber-600" />
                        <span class="text-sm font-medium">
                            <template v-if="remaining">Auto-unmutes in {{ remaining }}</template>
                            <template v-else>Until manually unmuted</template>
                        </span>
                    </div>
                    <div v-if="menu.muted_until" class="text-xs text-muted-foreground">
                        Until {{ formatDate(menu.muted_until) }}
                    </div>
                    <Separator class="bg-amber-200/40" />
                    <div v-if="menu.muted_reason" class="text-sm">
                        <p class="text-xs font-medium text-muted-foreground">Reason</p>
                        <div class="prose prose-sm dark:prose-invert max-w-none" v-html="menu.muted_reason" />
                    </div>
                    <div v-if="menu.muted_by_name || menu.muted_at" class="flex flex-wrap gap-x-4 gap-y-1 text-xs text-muted-foreground">
                        <span v-if="menu.muted_by_name" class="flex items-center gap-1">
                            <UserIcon class="h-3 w-3" /> {{ menu.muted_by_name }}
                        </span>
                        <span v-if="menu.muted_at">at {{ formatDate(menu.muted_at) }}</span>
                    </div>
                </div>
            </template>

            <!-- Not muted: choose duration + reason -->
            <template v-else>
                <div class="space-y-3">
                    <Label class="text-base font-medium">How long?</Label>
                    <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                        <Button
                            v-for="preset in presets"
                            :key="preset.key"
                            type="button"
                            class="h-11"
                            :variant="muteForm.preset === preset.key ? 'default' : 'outline'"
                            @click="muteForm.preset = preset.key"
                        >
                            {{ preset.label }}
                        </Button>
                    </div>
                    <p
                        v-if="muteForm.errors.preset"
                        class="text-xs text-destructive"
                    >{{ muteForm.errors.preset }}</p>
                </div>

                <div v-if="muteForm.preset === 'custom'" class="space-y-2">
                    <Label for="mute_until" class="text-sm">End time</Label>
                    <Input
                        id="mute_until"
                        v-model="muteForm.muted_until"
                        type="datetime-local"
                        :min="minDateTime"
                        class="h-11"
                    />
                    <p class="text-xs text-muted-foreground">Auto-unmutes at this time.</p>
                    <p
                        v-if="muteForm.errors.muted_until"
                        class="text-xs text-destructive"
                    >{{ muteForm.errors.muted_until }}</p>
                </div>

                <div class="space-y-2">
                    <Label for="mute_reason" class="text-sm">Reason <span class="text-muted-foreground">(optional)</span></Label>
                    <TiptapEditor
                        v-model="muteForm.reason"
                        placeholder="e.g. Out of stock, kitchen prep, 86'd"
                        min-height="150px"
                    />
                    <p
                        v-if="muteForm.errors.reason"
                        class="text-xs text-destructive"
                    >{{ muteForm.errors.reason }}</p>
                </div>
            </template>
        </div>
    </ModalForm>
</template>
