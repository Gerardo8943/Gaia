<!-- Tarjeta de telemetría que muestra cantidad, capacidad y porcentaje de un sistema vital. -->
<script lang="ts">
    import type { Snippet } from 'svelte';
    import { cn } from '@/lib/utils';

    let {
        name,
        quantity,
        capacity,
        percentage,
        measurementUnit,
        gradientClass = 'from-cyan-400 to-sky-600',
        icon,
    }: {
        name: string;
        quantity: number;
        capacity: number;
        percentage: number;
        measurementUnit: string;
        gradientClass?: string;
        icon?: Snippet;
    } = $props();
</script>

<div class="flex flex-col gap-3 rounded-xl border border-border bg-card p-5 shadow-sm">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
            {#if icon}
                {@render icon()}
            {/if}
            <span class="text-sm font-medium text-muted-foreground">{name}</span>
        </div>
        <span
            class={cn(
                'text-xs font-semibold',
                percentage <= 20 ? 'text-red-400' : percentage <= 40 ? 'text-amber-400' : 'text-emerald-400',
            )}
        >
            {percentage}%
        </span>
    </div>

    <div class="flex items-baseline gap-2">
        <span class="text-4xl font-bold tracking-tight tabular-nums">{quantity}</span>
        <span class="text-sm text-muted-foreground">
            {measurementUnit} / {capacity}
        </span>
    </div>

    <div class="relative h-6 w-full overflow-hidden rounded-full bg-zinc-900/80 dark:bg-zinc-800/80">
        <div
            class={cn('absolute inset-y-0 left-0 rounded-full bg-gradient-to-r transition-all duration-700', gradientClass)}
            style={`width: ${Math.min(100, Math.max(0, percentage))}%`}
        ></div>
    </div>
</div>
