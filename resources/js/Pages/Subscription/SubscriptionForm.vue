<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
    <div class="bg-white rounded-lg shadow-lg p-8 max-w-lg w-full relative">
      <button @click="onClose" class="absolute top-2 right-2 text-gray-400 hover:text-gray-600">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
      <h2 class="text-xl font-bold mb-6 text-gray-800">{{ mode === 'edit' ? 'Edit Subscription' : 'Add Subscription' }}</h2>
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
            <option v-for="freq in frequencies" :key="freq.value" :value="freq.value">{{ freq.label }}</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1">Currency</label>
          <select v-model="form.currency" class="w-full border border-gray-300 rounded-md px-4 py-2 focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition" required>
            <option v-for="cur in currencies" :key="cur.value" :value="cur.value">{{ cur.label }}</option>
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
          <button type="button" @click="onClose" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold px-6 py-2 rounded-md shadow transition">Cancel</button>
          <button :disabled="form.processing" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded-md shadow transition" type="submit">
            {{ mode === 'edit' ? 'Update' : 'Save' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { useForm, router } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
const props = defineProps({
  subscription: Object, // або null для create
  mode: String, // 'create' або 'edit'
  onClose: Function,
  onSuccess: Function,
  currencies: Array,
  frequencies: Array
});
const form = useForm({
  name: props.subscription?.name ?? '',
  cost: props.subscription?.cost ?? '',
  billing_frequency: props.subscription?.billing_frequency ?? 'monthly',
  currency: props.subscription?.currency ?? 'USD',
  start_date: props.subscription?.start_date ?? '',
  description: props.subscription?.description ?? ''
});

watch(() => props.subscription, (val) => {
  form.name = val?.name ?? '';
  form.cost = val?.cost ?? '';
  form.billing_frequency = val?.billing_frequency ?? 'monthly';
  form.currency = val?.currency ?? 'USD';
  form.start_date = val?.start_date ?? '';
  form.description = val?.description ?? '';
});

function submit() {
  if (props.mode === 'edit') {
    form.put(route('subscriptions.update', props.subscription.id), {
      onSuccess: () => {
        props.onSuccess && props.onSuccess();
        props.onClose && props.onClose();
      }
    });
  } else {
    form.post(route('subscriptions.store'), {
      onSuccess: () => {
        props.onSuccess && props.onSuccess();
        props.onClose && props.onClose();
      }
    });
  }
}
</script> 