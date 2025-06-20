<template>
    <img v-if="settings?.logo_url" :src="settings.logo_url" :alt="settings?.company_name || 'Company Logo'" class="h-12 w-auto">
    <svg v-else class="h-12 w-auto" viewBox="0 0 200 60" fill="none" xmlns="http://www.w3.org/2000/svg">
        <!-- Default Logo -->
        <defs>
            <linearGradient id="grad1" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" style="stop-color:#4F46E5;stop-opacity:1" />
                <stop offset="100%" style="stop-color:#7C3AED;stop-opacity:1" />
            </linearGradient>
        </defs>
        
        <!-- POS Icon -->
        <g transform="translate(10, 10)">
            <!-- Receipt/Register Icon -->
            <rect x="0" y="0" width="40" height="40" rx="8" fill="url(#grad1)"/>
            <rect x="5" y="8" width="30" height="4" rx="2" fill="white" opacity="0.9"/>
            <rect x="5" y="18" width="30" height="4" rx="2" fill="white" opacity="0.7"/>
            <rect x="5" y="28" width="20" height="4" rx="2" fill="white" opacity="0.5"/>
        </g>
        
        <!-- Text "POS" -->
        <text x="52" y="40" font-family="Arial, sans-serif" font-size="32" font-weight="bold" fill="#1F2937">
            <tspan fill="#4F46E5">POS</tspan>
            <tspan fill="#7C3AED">Kasir</tspan>
        </text>
    </svg>
</template>

<script setup>
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const settings = computed(() => usePage().props.settings);
</script>
