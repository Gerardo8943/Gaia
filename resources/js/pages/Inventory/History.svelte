<script module lang="ts">
    import { history as historyRoute } from '@/routes/inventory';
    import { index as inventoryIndex } from '@/routes/inventory';

    export const layout = {
        breadcrumbs: [
            {
                title: 'Inventario',
                href: inventoryIndex(),
            },
            {
                title: 'Historial',
                href: historyRoute(),
            },
        ],
    };
</script>

<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import ArrowRightLeft from 'lucide-svelte/icons/arrow-right-left';
    import History from 'lucide-svelte/icons/history';
    import AppHead from '@/components/common/AppHead.svelte';
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';
    import { toUrl } from '@/lib/utils';

    type TransferLogDto = {
        id: number;
        quantity: number;
        created_at: string;
        location_from: { id: number; name: string } | null;
        location_to: { id: number; name: string } | null;
        resource: { id: number; name: string; measurement_unit: string };
        user: { id: number; name: string } | null;
    };

    type PaginatorLink = {
        url: string | null;
        label: string;
        active: boolean;
    };

    type PaginatorDto = {
        data: TransferLogDto[];
        links: PaginatorLink[];
        current_page: number;
        last_page: number;
        from: number | null;
        to: number | null;
        total: number;
    };

    let { logs }: { logs: PaginatorDto } = $props();

    function formatDate(value: string): string {
        return new Date(value).toLocaleString('es-ES', {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        });
    }

    function decodeLabel(label: string): string {
        return label
            .replace(/&laquo;/g, '«')
            .replace(/&raquo;/g, '»')
            .replace(/&lt;/g, '<')
            .replace(/&gt;/g, '>')
            .replace(/&amp;/g, '&');
    }
</script>

<AppHead title="Historial de transferencias" />

<div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto p-4">
    <div class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-2">
            <History class="size-5 text-muted-foreground" />
            <h1 class="text-xl font-semibold">Historial de transferencias</h1>
        </div>
        <Button variant="outline" size="sm" asChild>
            <Link href={toUrl(inventoryIndex())}>
                <ArrowRightLeft class="size-4" />
                Volver al inventario
            </Link>
        </Button>
    </div>

    {#if logs.total === 0}
        <div class="flex flex-1 items-center justify-center rounded-xl border border-dashed border-border p-10">
            <p class="text-sm text-muted-foreground">
                Todavía no hay transferencias registradas.
            </p>
        </div>
    {:else}
        <div class="overflow-hidden rounded-xl border border-border bg-card shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-border text-xs uppercase tracking-wide text-muted-foreground">
                        <tr>
                            <th class="px-4 py-3 font-medium">Fecha</th>
                            <th class="px-4 py-3 font-medium">Recurso</th>
                            <th class="px-4 py-3 font-medium">Trayecto</th>
                            <th class="px-4 py-3 font-medium">Cantidad</th>
                            <th class="px-4 py-3 font-medium">Responsable</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        {#each logs.data as log (log.id)}
                            <tr class="transition-colors hover:bg-muted/50">
                                <td class="whitespace-nowrap px-4 py-3 text-muted-foreground">
                                    {formatDate(log.created_at)}
                                </td>
                                <td class="px-4 py-3 font-medium">
                                    {log.resource.name}
                                </td>
                                <td class="px-4 py-3">
                                    <span>{log.location_from?.name}</span>
                                    <span class="mx-1 text-muted-foreground"
                                        >→</span
                                    >
                                    <span>{log.location_to?.name}</span>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 tabular-nums">
                                    {log.quantity}
                                    {log.resource.measurement_unit}
                                </td>
                                <td class="px-4 py-3">
                                    {#if log.user}
                                        {log.user.name}
                                    {:else}
                                        <Badge variant="outline">Sistema</Badge>
                                    {/if}
                                </td>
                            </tr>
                        {/each}
                    </tbody>
                </table>
            </div>
        </div>

        {#if logs.last_page > 1}
            <div class="flex items-center justify-between gap-4 text-sm">
                <span class="text-muted-foreground">
                    Mostrando {logs.from}–{logs.to} de {logs.total}
                </span>
                <div class="flex items-center gap-1">
                    {#each logs.links as link (link.label + link.url)}
                        {#if link.url}
                            <Button
                                variant={link.active ? 'default' : 'outline'}
                                size="sm"
                                asChild
                            >
                                <Link
                                    href={link.url}
                                    preserve-scroll
                                    aria-label={link.label}
                                >
                                    {decodeLabel(link.label)}
                                </Link>
                            </Button>
                        {:else}
                            <span
                                class="px-2 py-1 text-muted-foreground"
                                aria-hidden="true"
                            >
                                {decodeLabel(link.label)}
                            </span>
                        {/if}
                    {/each}
                </div>
            </div>
        {/if}
    {/if}
</div>
