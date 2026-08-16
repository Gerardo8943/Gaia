<script module lang="ts">
    import { index as inventoryIndex } from '@/routes/inventory';

    export const layout = {
        breadcrumbs: [
            {
                title: 'Inventario',
                href: inventoryIndex(),
            },
        ],
    };
</script>

<script lang="ts">
    import { Link, router, useForm } from '@inertiajs/svelte';
    import ArrowRightLeft from 'lucide-svelte/icons/arrow-right-left';
    import Boxes from 'lucide-svelte/icons/boxes';
    import Clock3 from 'lucide-svelte/icons/clock-3';
    import Droplets from 'lucide-svelte/icons/droplets';
    import History from 'lucide-svelte/icons/history';
    import TriangleAlert from 'lucide-svelte/icons/triangle-alert';
    import Users from 'lucide-svelte/icons/users';
    import Utensils from 'lucide-svelte/icons/utensils';
    import Wind from 'lucide-svelte/icons/wind';
    import X from 'lucide-svelte/icons/x';
    import Zap from 'lucide-svelte/icons/zap';
    import AppHead from '@/components/common/AppHead.svelte';
    import StockBar from '@/components/inventory/StockBar.svelte';
    import TelemetryBar from '@/components/inventory/TelemetryBar.svelte';
    import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';
    import {
        Card,
        CardContent,
        CardDescription,
        CardHeader,
        CardTitle,
    } from '@/components/ui/card';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import { toUrl } from '@/lib/utils';
    import {
        history as historyRoute,
        preview as previewRoute,
        transfer as transferRoute,
    } from '@/routes/inventory';

    type ResourceDto = {
        id: number;
        name: string;
        measurement_unit: string;
        critical_threshold: number;
    };

    type StockDto = {
        id: number;
        quantity: number;
        status: string;
        location?: { id: number; name: string };
        resource: ResourceDto;
    };

    type LocationDto = {
        id: number;
        name: string;
        type: string;
        is_pressurized: boolean;
        occupants: number;
        stocks: StockDto[];
    };

    type TelemetryDto = {
        name: string;
        measurement_unit: string;
        quantity: number;
        capacity: number;
        percentage: number;
    };

    type ProjectionStockDto = {
        resource_name: string;
        measurement_unit: string;
        consumed: number;
        quantity: number;
        projected_quantity: number;
        status: string;
        projected_status: string;
        projected_hours_left?: number | null;
    };

    type ProjectionDto = {
        location: { id: number; name: string };
        hours: number;
        stocks: ProjectionStockDto[];
    };

    let {
        telemetry,
        criticalStocks,
        locations,
        projection = null,
    }: {
        telemetry: TelemetryDto[];
        criticalStocks: StockDto[];
        locations: LocationDto[];
        projection?: ProjectionDto | null;
    } = $props();

    const capacities = $derived(
        new Map(telemetry.map((metric) => [metric.name, metric.capacity])),
    );

    const resources = $derived([
        ...new Map(
            locations
                .flatMap((location) => location.stocks)
                .map((stock) => [stock.resource.id, stock.resource] as const),
        ).values(),
    ]);

    const transferForm = useForm({
        from_location_id: '',
        to_location_id: '',
        resource_id: '',
        quantity: '',
    });

    let previewHours = $state<Record<number, string>>({});
    let previewErrors = $state<Record<number, string>>({});
    let previewLoadingId = $state<number | null>(null);

    const selectClass =
        'dark:bg-zinc-950 mt-1 block w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs transition-[color,box-shadow] outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]';

    function submitTransfer(): void {
        transferForm.post(transferRoute.url(), {
            onSuccess: () => transferForm.reset(),
        });
    }

    function runPreview(locationId: number): void {
        const value = previewHours[locationId];
        const num = parseFloat(value);

        if (!value || isNaN(num) || num <= 0) {
            previewErrors[locationId] = 'Ingresa un número de horas válido (> 0).';
            return;
        }

        delete previewErrors[locationId];
        previewLoadingId = locationId;

        router.get(
            previewRoute(locationId).url,
            { hours: value },
            {
                preserveScroll: true,
                only: ['projection'],
                onFinish: () => {
                    previewLoadingId = null;
                },
            },
        );
    }
</script>

<AppHead title="Inventario" />

<div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto p-4">
    <div class="flex items-center justify-between gap-4">
        <h1 class="text-xl font-semibold">Telemetría de la base</h1>
        <div class="flex items-center gap-2">
            {#if criticalStocks.length > 0}
                <Badge variant="destructive" class="animate-pulse">
                    <TriangleAlert class="size-3" />
                    {criticalStocks.length}
                    {criticalStocks.length === 1 ? 'alerta' : 'alertas'}
                </Badge>
            {/if}
            <Button variant="outline" size="sm" asChild>
                <Link href={toUrl(historyRoute())}>
                    <History class="size-4" />
                    Historial
                </Link>
            </Button>
        </div>
    </div>

    {#if projection}
        <section
            aria-label="Previsión de consumo"
            class="grid gap-4 rounded-xl border border-sky-500/40 bg-sky-950/20 p-5 shadow-sm dark:border-sky-500/30"
        >
            <div class="flex items-center justify-between gap-4">
                <div class="flex items-center gap-2">
                    <Clock3 class="size-5 text-sky-400" />
                    <h2 class="text-base font-semibold text-sky-200">
                        Previsión de Soporte Vital: {projection.location.name}
                    </h2>
                    <Badge variant="outline" class="border-sky-400/50 bg-sky-500/10 text-sky-300">
                        {projection.hours}h simuladas
                    </Badge>
                </div>
                <Link
                    href={inventoryIndex().url}
                    class="inline-flex items-center gap-1 text-sm text-muted-foreground transition-colors hover:text-foreground"
                >
                    <X class="size-4" />
                    Descartar
                </Link>
            </div>

            <p class="text-sm text-muted-foreground">
                Proyección en tiempo real del impacto de soporte vital consumido durante {projection.hours} horas por los ocupantes activos.
            </p>

            {#if projection.stocks.length === 0}
                <p class="text-sm text-muted-foreground">
                    Esta ubicación no posee recursos consumibles registrados.
                </p>
            {:else}
                <div class="grid gap-3 md:grid-cols-2">
                    {#each projection.stocks as stock (stock.resource_name)}
                        <div
                            class="flex items-center justify-between gap-3 rounded-lg border border-border bg-card/60 px-4 py-3"
                        >
                            <div class="flex flex-col gap-1">
                                <span class="text-sm font-medium"
                                    >{stock.resource_name}</span
                                >
                                <span class="text-sm tabular-nums text-muted-foreground">
                                    {stock.quantity} → <strong class="text-foreground">{stock.projected_quantity}</strong>
                                    {stock.measurement_unit}
                                </span>
                                {#if stock.projected_hours_left !== undefined && stock.projected_hours_left !== null}
                                    <span class="text-xs text-sky-400">
                                        Autonomía restante: {stock.projected_hours_left}h
                                    </span>
                                {/if}
                            </div>
                            <div class="flex flex-col items-end gap-1">
                                <Badge
                                    variant="outline"
                                    class={stock.projected_status === 'Critico'
                                        ? 'border-red-500/60 bg-red-500/15 text-red-400'
                                        : stock.projected_status === 'Bajo'
                                          ? 'border-amber-500/50 bg-amber-500/15 text-amber-400'
                                          : 'border-emerald-500/50 bg-emerald-500/15 text-emerald-400'}
                                >
                                    {stock.status} → {stock.projected_status}
                                </Badge>
                                {#if stock.consumed > 0}
                                    <span class="text-xs text-muted-foreground tabular-nums"
                                        >−{stock.consumed}
                                        {stock.measurement_unit}</span
                                    >
                                {/if}
                            </div>
                        </div>
                    {/each}
                </div>
            {/if}
        </section>
    {/if}

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
                        {stock.resource.measurement_unit} · umbral
                        {stock.resource.critical_threshold} · {stock.status}
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
                    : metric.name.toLowerCase().includes('comida') || metric.name.toLowerCase().includes('raciones')
                      ? 'from-amber-400 to-orange-600'
                      : metric.name.toLowerCase().includes('energia') || metric.name.toLowerCase().includes('bateria')
                        ? 'from-yellow-400 to-amber-500'
                        : 'from-cyan-400 to-sky-600'}
            >
                {#snippet icon()}
                    {#if metric.name.toLowerCase().includes('agua')}
                        <Droplets class="size-5 text-sky-400" />
                    {:else if metric.name.toLowerCase().includes('comida') || metric.name.toLowerCase().includes('raciones')}
                        <Utensils class="size-5 text-amber-400" />
                    {:else if metric.name.toLowerCase().includes('energia') || metric.name.toLowerCase().includes('bateria')}
                        <Zap class="size-5 text-yellow-400" />
                    {:else}
                        <Wind class="size-5 text-cyan-400" />
                    {/if}
                {/snippet}
            </TelemetryBar>
        {/each}
    </section>

    <Card>
        <CardHeader>
            <CardTitle class="flex items-center gap-2">
                <ArrowRightLeft class="size-5" />
                Transferencia manual
            </CardTitle>
            <CardDescription
                >Mueve un recurso entre dos ubicaciones de la base.</CardDescription
            >
        </CardHeader>
        <CardContent>
            <form
                onsubmit={(event) => {
                    event.preventDefault();
                    submitTransfer();
                }}
                class="grid gap-4 md:grid-cols-4"
            >
                <div class="grid gap-2">
                    <Label for="from_location_id">Origen</Label>
                    <select
                        id="from_location_id"
                        name="from_location_id"
                        bind:value={transferForm.from_location_id}
                        class={selectClass}
                    >
                        <option value="" disabled>Selecciona origen</option>
                        {#each locations as location (location.id)}
                            <option value={location.id}>{location.name}</option>
                        {/each}
                    </select>
                    {#if transferForm.errors.from_location_id}
                        <p class="text-sm text-red-400">
                            {transferForm.errors.from_location_id}
                        </p>
                    {/if}
                </div>

                <div class="grid gap-2">
                    <Label for="to_location_id">Destino</Label>
                    <select
                        id="to_location_id"
                        name="to_location_id"
                        bind:value={transferForm.to_location_id}
                        class={selectClass}
                    >
                        <option value="" disabled>Selecciona destino</option>
                        {#each locations as location (location.id)}
                            <option value={location.id}>{location.name}</option>
                        {/each}
                    </select>
                    {#if transferForm.errors.to_location_id}
                        <p class="text-sm text-red-400">
                            {transferForm.errors.to_location_id}
                        </p>
                    {/if}
                </div>

                <div class="grid gap-2">
                    <Label for="resource_id">Recurso</Label>
                    <select
                        id="resource_id"
                        name="resource_id"
                        bind:value={transferForm.resource_id}
                        class={selectClass}
                    >
                        <option value="" disabled>Selecciona recurso</option>
                        {#each resources as resource (resource.id)}
                            <option value={resource.id}>{resource.name}</option>
                        {/each}
                    </select>
                    {#if transferForm.errors.resource_id}
                        <p class="text-sm text-red-400">
                            {transferForm.errors.resource_id}
                        </p>
                    {/if}
                </div>

                <div class="grid gap-2">
                    <Label for="quantity">Cantidad</Label>
                    <Input
                        id="quantity"
                        name="quantity"
                        type="number"
                        min="0.01"
                        step="0.01"
                        bind:value={transferForm.quantity}
                        placeholder="Ej: 50"
                        class="mt-1"
                    />
                    {#if transferForm.errors.quantity}
                        <p class="text-sm text-red-400">
                            {transferForm.errors.quantity}
                        </p>
                    {/if}
                </div>

                <div class="md:col-span-4">
                    <Button type="submit" disabled={transferForm.processing}>
                        <ArrowRightLeft class="size-4" />
                        {transferForm.processing
                            ? 'Transfiriendo...'
                            : 'Transferir'}
                    </Button>
                </div>
            </form>
        </CardContent>
    </Card>

    <section
        aria-label="Stock por ubicación"
        class="grid gap-4 md:grid-cols-2 xl:grid-cols-3"
    >
        {#each locations as location (location.id)}
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Boxes class="size-5 text-muted-foreground" />
                        <span>{location.name}</span>
                        {#if !location.is_pressurized}
                            <Badge variant="outline">Sin presión</Badge>
                        {/if}
                        <span
                            class="ms-auto flex items-center gap-1 text-sm font-normal text-muted-foreground"
                        >
                            <Users class="size-4" />
                            {location.occupants}
                        </span>
                    </CardTitle>
                    <CardDescription>{location.type}</CardDescription>
                </CardHeader>
                <CardContent class="gap-4">
                    {#if location.stocks.length === 0}
                        <p class="text-sm text-muted-foreground">
                            Sin recursos almacenados.
                        </p>
                    {:else}
                        {#each location.stocks as stock (stock.id)}
                            <StockBar
                                {stock}
                                capacity={capacities.get(stock.resource.name) ??
                                    null}
                            />
                        {/each}
                    {/if}

                    <form
                        onsubmit={(event) => {
                            event.preventDefault();
                            runPreview(location.id);
                        }}
                        class="flex flex-col gap-2 border-t pt-4"
                    >
                        <div class="flex items-end gap-2">
                            <div class="grid gap-1.5 flex-1">
                                <Label for={`hours-${location.id}`}
                                    >Simular horas de consumo</Label
                                >
                                <Input
                                    id={`hours-${location.id}`}
                                    name="hours"
                                    type="number"
                                    min="0.01"
                                    step="0.1"
                                    bind:value={previewHours[location.id]}
                                    placeholder="Ej: 24"
                                    class="w-full"
                                />
                            </div>
                            <Button
                                type="submit"
                                variant="outline"
                                disabled={previewLoadingId === location.id}
                            >
                                <Clock3 class="size-4" />
                                {previewLoadingId === location.id
                                    ? 'Calculando...'
                                    : 'Simular'}
                            </Button>
                        </div>

                        {#if previewErrors[location.id]}
                            <p class="text-xs text-red-400">
                                {previewErrors[location.id]}
                            </p>
                        {/if}
                    </form>
                </CardContent>
            </Card>
        {/each}
    </section>
</div>
