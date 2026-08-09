<script lang="ts">
    import type { Snippet } from 'svelte';
    import AppContent from '@/components/layout/AppContent.svelte';
    import AppShell from '@/components/layout/AppShell.svelte';
    import AppSidebar from '@/components/layout/AppSidebar.svelte';
    import AppSidebarHeader from '@/components/layout/AppSidebarHeader.svelte';
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

<AppShell variant="sidebar">
    <AppSidebar />
    <AppContent variant="sidebar" class="overflow-x-hidden">
        <AppSidebarHeader {breadcrumbs} />
        {@render children?.()}
    </AppContent>
    <Toaster />
</AppShell>
