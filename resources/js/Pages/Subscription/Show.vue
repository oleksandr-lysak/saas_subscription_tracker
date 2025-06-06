<template>
  <div class="max-w-xl mx-auto py-10 px-4">
    <div class="bg-white rounded-lg shadow-lg p-8">
      <h1 class="text-2xl font-extrabold mb-6 text-gray-800 flex items-center gap-2">
        <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2a2 2 0 012-2h2a2 2 0 012 2v2m-6 4h6a2 2 0 002-2v-6a2 2 0 00-2-2h-2a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
        Subscription Details
      </h1>
      <div class="space-y-3 text-gray-700">
        <div><span class="font-semibold">Name:</span> {{ subscription.name }}</div>
        <div><span class="font-semibold">Cost:</span> {{ subscription.cost }}</div>
        <div><span class="font-semibold">Frequency:</span> <span class="capitalize">{{ subscription.billing_frequency }}</span></div>
        <div><span class="font-semibold">Currency:</span> {{ subscription.currency }}</div>
        <div><span class="font-semibold">Start Date:</span> {{ subscription.start_date }}</div>
        <div><span class="font-semibold">Description:</span> {{ subscription.description }}</div>
      </div>
      <div class="flex justify-end mt-8">
        <Link :href="route('subscriptions.edit', subscription.id)" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded-md shadow transition">Edit</Link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
const props = defineProps({ subscription: Object });
</script> 