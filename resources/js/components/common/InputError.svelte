<!-- Muestra un mensaje de error de validación bajo un campo del formulario. -->
<script lang="ts">
    let {
        message = '',
        class: className = '',
    }: {
        message?: string;
        class?: string;
    } = $props();
</script>

{#if message}
    <div class={className}>
        <p class="text-sm text-red-600 dark:text-red-500">{message}</p>
    </div>
{/if}
