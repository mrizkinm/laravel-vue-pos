<template>
    <div v-if="links.length > 3">
        <div class="flex flex-wrap -mb-1">
            <template v-for="(link, key) in links" :key="key">
                <div v-if="link.url === null" 
                    class="mr-1 mb-1 px-4 py-2 text-sm text-gray-400 border rounded"
                    :class="{ 'ml-auto': link.label === 'Next &raquo;' }"
                    v-html="link.label"
                />
                <Link v-else
                    class="mr-1 mb-1 px-4 py-2 text-sm border rounded hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    :class="{
                        'bg-indigo-50 text-indigo-600': link.active,
                        'text-gray-700': !link.active,
                        'ml-auto': link.label === 'Next &raquo;'
                    }"
                    :href="link.url"
                    v-html="link.label"
                />
            </template>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    links: {
        type: Array,
        required: true
    }
});
</script> 