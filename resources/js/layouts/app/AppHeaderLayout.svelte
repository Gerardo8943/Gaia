<script lang="ts">
    import type { Snippet } from 'svelte';
    import AppContent from '@/components/layout/AppContent.svelte';
    import AppHeader from '@/components/layout/AppHeader.svelte';
    import AppShell from '@/components/layout/AppShell.svelte';
    import { Toaster } from '@/components/ui/sonner';
    import type { BreadcrumbItem } from '@/types';

    let {
        breadcrumbs = [],
        children,
    }: {
        breadcrumbs?: BreadcrumbItem[];
        children?: Snippet;
    } = $props();
</script>

<AppShell variant="header">
    <AppHeader {breadcrumbs} />
    <AppContent variant="header">
        {@render children?.()}
    </AppContent>
    <Toaster />
</AppShell>
