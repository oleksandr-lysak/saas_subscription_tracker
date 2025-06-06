<template>
  <div class="max-w-xl mx-auto py-10 px-4">
    <div class="bg-white rounded-lg shadow-lg p-8">
      <h1 class="text-2xl font-extrabold mb-6 text-gray-800">Add Subscription</h1>
      <form @submit.prevent="submit" class="space-y-5">
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1">Name</label>
          <input v-model="form.name" class="w-full border border-gray-300 rounded-md px-4 py-2 focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition" required />
        </div>
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1">Cost</label>
          <input v-model="form.cost" type="number" step="0.01" class="w-full border border-gray-300 rounded-md px-4 py-2 focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition" required />
        </div>
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1">Frequency</label>
          <select v-model="form.billing_frequency" class="w-full border border-gray-300 rounded-md px-4 py-2 focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition" required>
            <option value="monthly">Monthly</option>
            <option value="yearly">Yearly</option>
            <option value="weekly">Weekly</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1">Currency</label>
          <select v-model="form.currency" class="w-full border border-gray-300 rounded-md px-4 py-2 focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition" required>
            <option value="USD">USD</option>
            <option value="EUR">EUR</option>
            <option value="UAH">UAH</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1">Start Date</label>
          <input v-model="form.start_date" type="date" class="w-full border border-gray-300 rounded-md px-4 py-2 focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition" required />
        </div>
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1">Description</label>
          <textarea v-model="form.description" class="w-full border border-gray-300 rounded-md px-4 py-2 focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition" rows="3"></textarea>
        </div>
        <div class="flex justify-end gap-2 mt-6">
          <button class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold px-6 py-2 rounded-md shadow transition" type="reset" @click="form.reset()">Reset</button>
          <button class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded-md shadow transition" type="submit">Save</button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { useForm, router, Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
const form = useForm({
  name: '',
  cost: '',
  billing_frequency: 'monthly',
  currency: 'USD',
  start_date: '',
  description: ''
});
function submit() {
  form.post(route('subscriptions.store'));
}
</script> 