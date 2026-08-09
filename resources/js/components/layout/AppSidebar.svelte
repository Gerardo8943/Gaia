<!-- Barra lateral colapsable con el logo, navegación principal y el usuario en el pie. -->
<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import BookOpen from 'lucide-svelte/icons/book-open';
    import Boxes from 'lucide-svelte/icons/boxes';
    import FolderGit2 from 'lucide-svelte/icons/folder-git-2';
    import LayoutGrid from 'lucide-svelte/icons/layout-grid';
    import type { Snippet } from 'svelte';
    import AppLogo from '@/components/branding/AppLogo.svelte';
    import NavFooter from '@/components/navigation/NavFooter.svelte';
    import NavMain from '@/components/navigation/NavMain.svelte';
    import NavUser from '@/components/navigation/NavUser.svelte';
    import {
        Sidebar,
        SidebarContent,
        SidebarFooter,
        SidebarHeader,
        SidebarMenu,
        SidebarMenuButton,
        SidebarMenuItem,
    } from '@/components/ui/sidebar';
    import { toUrl } from '@/lib/utils';
    import { dashboard } from '@/routes';
    import { index as inventoryIndex } from '@/routes/inventory';
    import type { NavItem } from '@/types';

    let {
        children,
    }: {
        children?: Snippet;
    } = $props();

    const mainNavItems: NavItem[] = [
        {
            title: 'Dashboard',
            href: dashboard(),
            icon: LayoutGrid,
        },
        {
            title: 'Inventario',
            href: inventoryIndex(),
            icon: Boxes,
        },
    ];

    const footerNavItems: NavItem[] = [
        {
            title: 'Repository',
            href: 'https://github.com/laravel/svelte-starter-kit',
            icon: FolderGit2,
        },
        {
            title: 'Documentation',
            href: 'https://laravel.com/docs/starter-kits#svelte',
            icon: BookOpen,
        },
    ];
</script>

<Sidebar collapsible="icon" variant="inset">
    <SidebarHeader>
        <SidebarMenu>
            <SidebarMenuItem>
                <SidebarMenuButton size="lg" asChild>
                    {#snippet children(props)}
                        <Link
                            {...props}
                            href={toUrl(dashboard())}
                            class={props.class}
                        >
                            <AppLogo />
                        </Link>
                    {/snippet}
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarHeader>

    <SidebarContent>
        <NavMain items={mainNavItems} />
    </SidebarContent>

    <SidebarFooter>
        <NavFooter items={footerNavItems} />
        <NavUser />
    </SidebarFooter>
</Sidebar>
{@render children?.()}
