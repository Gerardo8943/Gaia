<!-- Establece el título de la página en el <head> usando el nombre de la aplicación y un título opcional. -->
<script lang="ts">
    import type { Snippet } from 'svelte';

    let {
        title = '',
        children,
    }: {
        title?: string;
        children?: Snippet;
    } = $props();

    const appName = import.meta.env.VITE_APP_NAME || 'Laravel';
    const fullTitle = $derived(title ? `${title} - ${appName}` : appName);
</script>

<svelte:head>
    <title>{fullTitle}</title>
    {@render children?.()}
</svelte:head>
