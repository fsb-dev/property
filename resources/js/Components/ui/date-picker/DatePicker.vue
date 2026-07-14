<script setup>
import { ref, computed } from 'vue';
import { onClickOutside } from '@vueuse/core';
import { cn } from '@/lib/utils';

const props = defineProps({
    modelValue: { type: String, default: null }, // YYYY-MM-DD
    placeholder: { type: String, default: 'Pick a date' },
    class: { type: [String, Array, Object], default: '' },
});
const emit = defineEmits(['update:modelValue']);

const open = ref(false);
const triggerRef = ref(null);
const dropdownRef = ref(null);
const dropdownStyle = ref({});

// Close when clicking outside both trigger and dropdown
onClickOutside(dropdownRef, () => closeDropdown(), { ignore: [triggerRef] });

const today = new Date();

const selected = computed(() => {
    if (!props.modelValue) return null;
    const [y, m, d] = props.modelValue.split('-').map(Number);
    return new Date(y, m - 1, d);
});

const base = selected.value ?? today;
const viewDate = ref(new Date(base.getFullYear(), base.getMonth(), 1));

const viewYear  = computed(() => viewDate.value.getFullYear());
const viewMonth = computed(() => viewDate.value.getMonth());

const MONTHS = ['January','February','March','April','May','June',
                'July','August','September','October','November','December'];
const DAYS   = ['Su','Mo','Tu','We','Th','Fr','Sa'];

const calendarDays = computed(() => {
    const firstWeekday = new Date(viewYear.value, viewMonth.value, 1).getDay();
    const daysInMonth  = new Date(viewYear.value, viewMonth.value + 1, 0).getDate();
    const prevDays     = new Date(viewYear.value, viewMonth.value, 0).getDate();
    const cells = [];

    for (let i = 0; i < firstWeekday; i++) {
        cells.push({ day: prevDays - firstWeekday + i + 1, current: false, date: null });
    }
    for (let d = 1; d <= daysInMonth; d++) {
        const mm = String(viewMonth.value + 1).padStart(2, '0');
        const dd = String(d).padStart(2, '0');
        cells.push({ day: d, current: true, date: `${viewYear.value}-${mm}-${dd}` });
    }
    const remaining = 42 - cells.length;
    for (let i = 1; i <= remaining; i++) {
        cells.push({ day: i, current: false, date: null });
    }
    return cells;
});

function calcPosition() {
    if (!triggerRef.value) return;
    const rect = triggerRef.value.getBoundingClientRect();
    dropdownStyle.value = {
        position: 'fixed',
        top:  `${rect.bottom + 6}px`,
        left: `${rect.left}px`,
        width: `${Math.max(rect.width, 288)}px`,
        zIndex: 9999,
    };
}

function openDropdown() {
    calcPosition();
    window.addEventListener('scroll', calcPosition, { capture: true, passive: true });
    window.addEventListener('resize', calcPosition, { passive: true });
    open.value = true;
}

function closeDropdown() {
    open.value = false;
    window.removeEventListener('scroll', calcPosition, true);
    window.removeEventListener('resize', calcPosition);
}

function toggle() {
    open.value ? closeDropdown() : openDropdown();
}

function prevMonth() { viewDate.value = new Date(viewYear.value, viewMonth.value - 1, 1); }
function nextMonth() { viewDate.value = new Date(viewYear.value, viewMonth.value + 1, 1); }

function select(cell) {
    if (!cell.current) return;
    emit('update:modelValue', cell.date);
    closeDropdown();
}

function clear() { emit('update:modelValue', null); }

const isSelected = (cell) => cell.date && props.modelValue === cell.date;
const isToday    = (cell) => {
    if (!cell.date) return false;
    const [y, m, d] = cell.date.split('-').map(Number);
    return today.getFullYear() === y && today.getMonth() === m - 1 && today.getDate() === d;
};

const displayValue = computed(() => {
    if (!selected.value) return null;
    return selected.value.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
});
</script>

<template>
    <!-- Trigger wrapper -->
    <div ref="triggerRef">
        <button
            type="button"
            @click="toggle"
            :class="cn(
                'flex h-10 w-full items-center gap-2.5 px-3 text-sm text-left transition-colors',
                props.class
            )"
        >
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="flex-none text-muted-foreground">
                <rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>
            </svg>
            <span :class="modelValue ? 'text-foreground' : 'text-muted-foreground'">
                {{ displayValue ?? placeholder }}
            </span>

            <!-- Clear -->
            <button
                v-if="modelValue"
                type="button"
                @click.stop="clear"
                class="ml-auto flex h-5 w-5 flex-none items-center justify-center rounded text-muted-foreground transition-colors hover:text-foreground"
            >
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>

            <!-- Chevron -->
            <svg
                v-else
                width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                class="ml-auto flex-none text-muted-foreground transition-transform duration-150"
                :class="open ? '-rotate-180' : ''"
            ><path d="m6 9 6 6 6-6"/></svg>
        </button>
    </div>

    <!-- Calendar — teleported to body to escape overflow:hidden parents -->
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-150 ease-out origin-top"
            enter-from-class="opacity-0 scale-y-95"
            enter-to-class="opacity-100 scale-y-100"
            leave-active-class="transition duration-100 ease-in origin-top"
            leave-from-class="opacity-100 scale-y-100"
            leave-to-class="opacity-0 scale-y-95"
        >
            <div
                v-if="open"
                ref="dropdownRef"
                :style="dropdownStyle"
                class="overflow-hidden rounded-xl border border-border bg-admin-surface-card shadow-xl shadow-black/10 dark:shadow-black/50"
            >
                <!-- Month navigation -->
                <div class="flex items-center justify-between border-b border-border px-3 py-2">
                    <button type="button" @click="prevMonth" class="flex h-6 w-6 items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-muted hover:text-foreground">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>
                    </button>
                    <span class="text-xs font-semibold text-foreground">{{ MONTHS[viewMonth] }} {{ viewYear }}</span>
                    <button type="button" @click="nextMonth" class="flex h-6 w-6 items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-muted hover:text-foreground">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
                    </button>
                </div>

                <div class="p-2">
                    <!-- Day-of-week headers -->
                    <div class="mb-0.5 grid grid-cols-7">
                        <div v-for="d in DAYS" :key="d" class="flex h-6 items-center justify-center text-[10px] font-medium text-muted-foreground">
                            {{ d }}
                        </div>
                    </div>

                    <!-- Day cells -->
                    <div class="grid grid-cols-7 gap-px">
                        <button
                            v-for="(cell, i) in calendarDays"
                            :key="i"
                            type="button"
                            @click="select(cell)"
                            :disabled="!cell.current"
                            :class="[
                                'flex h-7 w-full items-center justify-center rounded-md text-xs transition-colors',
                                !cell.current && 'pointer-events-none opacity-25 text-muted-foreground',
                                cell.current && isSelected(cell) && 'bg-admin-accent text-on-gold font-semibold',
                                cell.current && !isSelected(cell) && isToday(cell) && 'text-admin-accent font-semibold ring-1 ring-admin-accent/30',
                                cell.current && !isSelected(cell) && !isToday(cell) && 'text-foreground hover:bg-muted',
                            ]"
                        >
                            {{ cell.day }}
                        </button>
                    </div>
                </div>

                <!-- Today shortcut -->
                <div class="border-t border-border px-3 py-1.5">
                    <button
                        type="button"
                        @click="select({ current: true, date: today.toISOString().split('T')[0] })"
                        class="text-xs font-medium text-admin-accent transition-colors hover:text-admin-accent/80"
                    >
                        Today
                    </button>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
