<script module lang="ts">
    import { dashboard } from '@/routes';
    import { index as inventoryIndex } from '@/routes/inventory';

    export const layout = {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    };
</script>

<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import ArrowRightLeft from 'lucide-svelte/icons/arrow-right-left';
    import Boxes from 'lucide-svelte/icons/boxes';
    import Droplets from 'lucide-svelte/icons/droplets';
    import MapPin from 'lucide-svelte/icons/map-pin';
    import Timer from 'lucide-svelte/icons/timer';
    import TriangleAlert from 'lucide-svelte/icons/triangle-alert';
    import Users from 'lucide-svelte/icons/users';
    import Wind from 'lucide-svelte/icons/wind';
    import AppHead from '@/components/common/AppHead.svelte';
    import TelemetryBar from '@/components/inventory/TelemetryBar.svelte';
    import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';
    import {
        Card,
        CardContent,
        CardHeader,
        CardTitle,
    } from '@/components/ui/card';
    import { cn, toUrl } from '@/lib/utils';

    type TelemetryDto = {
        name: string;
        measurement_unit: string;
        quantity: number;
        capacity: number;
        percentage: number;
    };

    type CriticalStockDto = {
        id: number;
        quantity: number;
        status: string;
        location?: { id: number; name: string };
        resource: {
            id: number;
            name: string;
            measurement_unit: string;
            critical_threshold: number;
        };
    };

    type AutonomyStockDto = {
        resource_name: string;
        measurement_unit: string;
        quantity: number;
        status: string;
        hours: number | null;
    };

    type AutonomyLocationDto = {
        location: { id: number; name: string; type: string; occupants: number };
        stocks: AutonomyStockDto[];
    };

    type KpisDto = {
        occupants: number;
        locations: number;
        critical_stocks: number;
        oxygen_percentage: number;
        water_percentage: number;
        min_autonomy_hours: number | null;
    };

    let {
        kpis,
        telemetry,
        criticalStocks,
        autonomy,
    }: {
        kpis: KpisDto;
        telemetry: TelemetryDto[];
        criticalStocks: CriticalStockDto[];
        autonomy: AutonomyLocationDto[];
    } = $props();

    function formatAutonomy(hours: number | null): string {
        if (hours === null) {
            return 'Estable';
        }

        if (hours < 24) {
            return `${hours.toFixed(1)}h`;
        }

        const days = Math.floor(hours / 24);
        const remainder = Math.round(hours % 24);

        return remainder > 0 ? `${days}d ${remainder}h` : `${days}d`;
    }

    function statusClass(status: string): string {
        if (status === 'Critico') {
            return 'border-red-500/60 bg-red-500/15 text-red-400';
        }

        if (status === 'Bajo') {
            return 'border-amber-500/50 bg-amber-500/15 text-amber-400';
        }

        return 'border-emerald-500/50 bg-emerald-500/15 text-emerald-400';
    }

    const kpiCards = $derived([
        {
            label: 'Ocupantes',
            value: kpis.occupants,
            icon: Users,
        },
        {
            label: 'Ubicaciones',
            value: kpis.locations,
            icon: MapPin,
        },
        {
            label: 'Alertas',
            value: kpis.critical_stocks,
            icon: TriangleAlert,
            danger: kpis.critical_stocks > 0,
        },
        {
            label: 'O₂ global',
            value: `${kpis.oxygen_percentage}%`,
            icon: Wind,
        },
        {
            label: 'Agua global',
            value: `${kpis.water_percentage}%`,
            icon: Droplets,
        },
        {
            label: 'Autonomía mínima',
            value: formatAutonomy(kpis.min_autonomy_hours),
            icon: Timer,
        },
    ]);
</script>

<AppHead title="Dashboard" />

<div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto p-4">
    <div class="flex items-center justify-between gap-4">
        <h1 class="text-xl font-semibold">Vista de mando</h1>
        <Button variant="outline" size="sm" asChild>
            <Link href={toUrl(inventoryIndex())}>
                <ArrowRightLeft class="size-4" />
                Inventario
            </Link>
        </Button>
    </div>

    <section
        aria-label="Indicadores clave"
        class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6"
    >
        {#each kpiCards as card (card.label)}
            <div
                class={cn(
                    'flex flex-col gap-2 rounded-xl border border-border bg-card p-4 shadow-sm',
                    card.danger && 'border-red-500/60',
                )}
            >
                <div class="flex items-center justify-between">
                    <span
                        class="text-xs font-medium uppercase tracking-wide text-muted-foreground"
                    >
                        {card.label}
                    </span>
                    <card.icon
                        class={cn(
                            'size-4 text-muted-foreground',
                            card.danger && 'animate-pulse text-red-400',
                        )}
                    />
                </div>
                <span
                    class={cn(
                        'text-2xl font-bold tracking-tight tabular-nums',
                        card.danger && 'text-red-400',
                    )}
                >
                    {card.value}
                </span>
            </div>
        {/each}
    </section>

    {#if criticalStocks.length > 0}
        <section
            aria-label="Alertas críticas"
            class="grid gap-3 md:grid-cols-2 xl:grid-cols-3"
        >
            {#each criticalStocks as stock (stock.id)}
                <Alert
                    variant={stock.status === 'Critico'
                        ? 'destructive'
                        : 'default'}
                    class={stock.status === 'Critico'
                        ? 'animate-pulse border-red-500/60 bg-red-500/10'
                        : 'border-amber-500/50 bg-amber-500/10'}
                >
                    <TriangleAlert class="size-4" />
                    <AlertTitle>
                        {stock.resource.name} — {stock.location?.name}
                    </AlertTitle>
                    <AlertDescription>
                        {stock.quantity}
                        {stock.resource.measurement_unit} ·
                        {stock.status}
                    </AlertDescription>
                </Alert>
            {/each}
        </section>
    {/if}

    <section aria-label="Niveles globales" class="grid gap-4 md:grid-cols-2">
        {#each telemetry as metric (metric.name)}
            <TelemetryBar
                name={metric.name}
                quantity={metric.quantity}
                capacity={metric.capacity}
                percentage={metric.percentage}
                measurementUnit={metric.measurement_unit}
                gradientClass={metric.name.toLowerCase().includes('agua')
                    ? 'from-sky-400 to-blue-600'
                    : 'from-cyan-400 to-sky-600'}
            >
                {#snippet icon()}
                    {#if metric.name.toLowerCase().includes('agua')}
                        <Droplets class="size-5 text-muted-foreground" />
                    {:else}
                        <Wind class="size-5 text-muted-foreground" />
                    {/if}
                {/snippet}
            </TelemetryBar>
        {/each}
    </section>

    <section aria-label="Autonomía por ubicación">
        <h2 class="mb-3 flex items-center gap-2 text-base font-semibold">
            <Timer class="size-4 text-muted-foreground" />
            Autonomía estimada por ubicación
        </h2>
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            {#each autonomy as entry (entry.location.id)}
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2 text-sm">
                            <Boxes class="size-4 text-muted-foreground" />
                            <span>{entry.location.name}</span>
                            <span
                                class="ms-auto flex items-center gap-1 text-sm font-normal text-muted-foreground"
                            >
                                <Users class="size-4" />
                                {entry.location.occupants}
                            </span>
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="gap-3">
                        {#if entry.stocks.length === 0}
                            <p class="text-sm text-muted-foreground">
                                Sin recursos consumibles.
                            </p>
                        {:else}
                            {#each entry.stocks as stock (stock.resource_name)}
                                <div
                                    class="flex items-center justify-between gap-3"
                                >
                                    <div class="flex flex-col">
                                        <span class="text-sm font-medium">
                                            {stock.resource_name}
                                        </span>
                                        <span
                                            class="text-sm tabular-nums text-muted-foreground"
                                        >
                                            {stock.quantity}
                                            {stock.measurement_unit}
                                        </span>
                                    </div>
                                    <div class="flex flex-col items-end gap-1">
                                        <Badge
                                            class={statusClass(stock.status)}
                                        >
                                            {stock.status}
                                        </Badge>
                                        <span
                                            class="text-xs text-muted-foreground"
                                        >
                                            {formatAutonomy(stock.hours)}
                                        </span>
                                    </div>
                                </div>
                            {/each}
                        {/if}
                    </CardContent>
                </Card>
            {/each}
        </div>
    </section>
</div>
