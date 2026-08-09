<!-- Barra de estado del stock de un recurso por ubicación, con insignia de estado y porcentaje según capacidad. -->
<script lang="ts">
    import { Badge } from '@/components/ui/badge';
    import { cn } from '@/lib/utils';

    type StockDto = {
        id: number;
        quantity: number;
        status: string;
        resource: {
            id: number;
            name: string;
            measurement_unit: string;
            critical_threshold: number;
        };
    };

    const statusMeta = {
        Optimo: {
            label: 'Optimo',
            className:
                'border-emerald-500/50 bg-emerald-500/15 text-emerald-400',
            barClass: 'from-emerald-500 to-emerald-400',
        },
        Bajo: {
            label: 'Bajo',
            className: 'border-amber-500/50 bg-amber-500/15 text-amber-400',
            barClass: 'from-amber-500 to-amber-400',
        },
        Critico: {
            label: 'Critico',
            className:
                'border-red-500/60 bg-red-500/20 text-red-400 animate-pulse',
            barClass: 'from-red-600 to-red-500',
        },
    } as const;

    let {
        stock,
        capacity = null,
    }: {
        stock: StockDto;
        capacity?: number | null;
    } = $props();

    const meta = $derived(
        statusMeta[stock.status as keyof typeof statusMeta] ??
            statusMeta.Optimo,
    );
    const percentage = $derived(
        capacity != null && capacity > 0
            ? Math.min(100, Math.max(0, (stock.quantity / capacity) * 100))
            : null,
    );
</script>

<div class="flex flex-col gap-1.5">
    <div class="flex items-center justify-between gap-2">
        <span class="text-sm font-medium">{stock.resource.name}</span>
        <div class="flex items-center gap-2">
            <span class="text-sm tabular-nums text-muted-foreground">
                {stock.quantity}
                {stock.resource.measurement_unit}
            </span>
            <Badge class={meta.className}>{meta.label}</Badge>
        </div>
    </div>

    {#if percentage !== null}
        <div
            class="relative h-3 w-full overflow-hidden rounded-full bg-zinc-900/80 dark:bg-zinc-800/80"
        >
            <div
                class={cn(
                    'absolute inset-y-0 left-0 rounded-full bg-gradient-to-r transition-all duration-700',
                    meta.barClass,
                )}
                style={`width: ${percentage}%`}
            ></div>
        </div>
    {/if}
</div>
