<template>
  <div class="max-w-5xl mx-auto py-8 px-4">
    <h1 class="text-3xl font-extrabold mb-8 text-gray-800">SaaS Subscriptions</h1>

    <!-- Вибір базової валюти окремо -->
    <div class="mb-6 flex items-center gap-4">
      <label class="block text-sm font-semibold text-gray-700">Base Currency:</label>
      <select v-model="filters.base_currency" @change="onBaseCurrencyChange" class="input input-bordered rounded-md border-gray-300 focus:ring-2 focus:ring-blue-400">
        <option v-for="cur in props.currencies" :key="cur.value" :value="cur.value">{{ cur.label }}</option>
      </select>
      <span class="text-xs text-gray-500">All price filters use this currency for conversion.</span>
    </div>

    
    <div class="mb-4 text-xs text-gray-500">Price range is applied to the converted value in the selected base currency.</div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
      <div class="bg-white rounded-lg shadow p-6">
        <div class="mb-2 text-gray-600">Total projected expense <span class="font-semibold">(30 days)</span>:</div>
        <div class="text-2xl font-bold text-blue-700">{{ formatAmount(total30) }} {{ filters.base_currency }}</div>
        <div class="mb-2 mt-4 text-gray-600">Total projected expense <span class="font-semibold">(365 days)</span>:</div>
        <div class="text-2xl font-bold text-blue-700">{{ formatAmount(total365) }} {{ filters.base_currency }}</div>
      </div>
      <div class="bg-white rounded-lg shadow p-6">
        <h2 class="font-semibold text-lg mb-2 text-gray-700">Top 5 Most Expensive</h2>
        <ul class="space-y-1">
          <li v-for="sub in top.data" :key="sub.id" class="flex justify-between">
            <span class="font-medium text-gray-800">{{ sub.name }}</span>
            <span class="text-blue-600">{{ formatAmount(sub.cost * getRate(sub.currency)) }} {{ filters.base_currency }}</span>
          </li>
        </ul>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
      <div class="bg-white rounded-lg shadow p-6">
        <h2 class="font-semibold text-lg mb-2 text-gray-700">Grouped Statistics</h2>
        <ul class="space-y-1">
          <li v-for="(group, freq) in grouped" :key="freq">
            <span class="font-medium capitalize">{{ freq }}</span>:
            <span class="text-gray-700">count <b>{{ group.count }}</b>, total <b>{{ formatAmount(group.total) }}</b> {{ filters.base_currency }}, avg <b>{{ formatAmount(group.avg) }}</b> {{ filters.base_currency }}</span>
          </li>
        </ul>
      </div>
      <div class="bg-white rounded-lg shadow p-6">
        <h2 class="font-semibold text-lg mb-2 text-gray-700">Currency-based Summary</h2>
        <ul class="space-y-1">
          <li v-for="summary in currencySummary" :key="summary.currency">
            <span class="font-medium">{{ summary.currency }}</span>: avg <span class="text-blue-600">{{ formatAmount(summary.avg) }}</span>
          </li>
        </ul>
      </div>
    </div>
    <form class="mb-8 flex flex-wrap gap-4 items-end bg-white p-4 rounded-lg shadow-md" @submit.prevent="applyFilters">
      <div class="flex-1 min-w-[150px]">
        <label class="block text-xs font-semibold text-gray-600 mb-1">Frequency</label>
        <select v-model="filters.billing_frequency" class="w-full input input-bordered rounded-md border-gray-300 focus:ring-2 focus:ring-blue-400">
          <option value="">All</option>
          <option v-for="freq in props.frequencies" :key="freq.value" :value="freq.value">{{ freq.label }}</option>
        </select>
      </div>
      <div class="flex-1 min-w-[150px]">
        <label class="block text-xs font-semibold text-gray-600 mb-1">Min Price (in base currency)</label>
        <input v-model.number="filters.min_price" type="number" step="0.01" class="w-full input input-bordered rounded-md border-gray-300 focus:ring-2 focus:ring-blue-400" />
      </div>
      <div class="flex-1 min-w-[150px]">
        <label class="block text-xs font-semibold text-gray-600 mb-1">Max Price (in base currency)</label>
        <input v-model.number="filters.max_price" type="number" step="0.01" class="w-full input input-bordered rounded-md border-gray-300 focus:ring-2 focus:ring-blue-400" />
      </div>
      <div class="flex-1 min-w-[150px]">
        <label class="block text-xs font-semibold text-gray-600 mb-1">Month (1-12)</label>
        <input v-model.number="filters.month" type="number" min="1" max="12" class="w-full input input-bordered rounded-md border-gray-300 focus:ring-2 focus:ring-blue-400" />
      </div>
      <button class="btn bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-md shadow transition" type="submit">Apply</button>
      <button class="btn bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold px-4 py-2 rounded-md shadow transition ml-2" type="button" @click="resetFilters">Reset</button>
    </form>
    <div class="flex justify-end mb-4">
      <button @click="openAddModal" class="btn bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-2 rounded-md shadow transition flex items-center">
        <svg class="w-5 h-5 inline-block mr-2 -mt-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        Add Subscription
      </button>
    </div>
    <div class="overflow-x-auto">
      <div class="rounded-xl shadow-lg bg-white overflow-hidden border border-gray-100">
        <table class="min-w-full">
          <thead class="bg-blue-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Name</th>
              <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Cost</th>
              <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Currency</th>
              <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Frequency</th>
              <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Start Date</th>
              <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="subscription in subscriptions.data" :key="subscription.id" class="hover:bg-blue-50 transition group border-b last:border-b-0 border-gray-100">
              <td class="px-6 py-3 font-medium text-gray-800 whitespace-nowrap align-middle">{{ subscription.name }}</td>
              <td class="px-6 py-3 whitespace-nowrap align-middle text-right">
                <span class="inline-block bg-gray-50 rounded px-2 py-1 font-mono text-sm text-gray-700 shadow-sm">
                  {{ formatAmount(subscription.cost) }}
                </span>
              </td>
              <td class="px-6 py-3 align-middle">
                <span :class="{
                  'bg-blue-50 text-blue-700': subscription.currency === 'USD',
                  'bg-green-50 text-green-700': subscription.currency === 'EUR',
                  'bg-yellow-50 text-yellow-700': subscription.currency === 'UAH',
                  'px-2 py-1 rounded-full text-xs font-semibold': true
                }">
                  {{ subscription.currency }}
                </span>
              </td>
              <td class="px-6 py-3 align-middle">
                <span :class="{
                  'bg-blue-100 text-blue-700': subscription.billing_frequency === 'monthly',
                  'bg-green-100 text-green-700': subscription.billing_frequency === 'yearly',
                  'bg-yellow-100 text-yellow-700': subscription.billing_frequency === 'weekly',
                  'px-2 py-1 rounded-full text-xs font-semibold': true
                }">
                  {{ subscription.billing_frequency }}
                </span>
              </td>
              
              <td class="px-6 py-3 whitespace-nowrap align-middle">{{ subscription.formatted_start_date }}</td>
              <td class="px-6 py-3 flex gap-2 align-middle">
                <button @click="openEditModal(subscription)" class="text-blue-600 hover:text-blue-800 transition" title="Edit">
                  <svg class="w-5 h-5 inline-block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536M9 11l6 6M3 21h6l11-11a2.828 2.828 0 00-4-4L5 17v4z"/></svg>
                </button>
                <button @click="askDelete(subscription.id, subscription.name)" class="text-red-600 hover:text-red-800 transition" title="Delete">
                  <svg class="w-5 h-5 inline-block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Subscription Form Modal -->
    <SubscriptionForm v-if="showFormModal" :mode="formMode" :subscription="editSubscription" :onClose="closeFormModal" :onSuccess="refreshList" :currencies="props.currencies" :frequencies="props.frequencies" />

    <!-- Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
      <div class="bg-white rounded-lg shadow-lg p-8 max-w-sm w-full">
        <h2 class="text-lg font-bold mb-4 text-gray-800">Confirm Deletion</h2>
        <p class="mb-6 text-gray-600">Are you sure you want to delete <span class="font-semibold">{{ toDeleteName }}</span>? This action cannot be undone.</p>
        <div class="flex justify-end gap-2">
          <button @click="cancelDelete" class="px-4 py-2 rounded bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold">Cancel</button>
          <button @click="confirmDelete" class="px-4 py-2 rounded bg-red-600 hover:bg-red-700 text-white font-semibold">Delete</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { router, Link } from '@inertiajs/vue3';
import { reactive, ref, watch, onMounted } from 'vue';
import { route } from 'ziggy-js';
import SubscriptionForm from './SubscriptionForm.vue';

const props = defineProps({
  subscriptions: Object,
  filters: Object,
  top: Object,
  grouped: Object,
  currencySummary: Object,
  total30: Number,
  total365: Number,
  currencies: Array,
  frequencies: Array
});
const filters = reactive({ ...props.filters });

// --- Base currency persistence ---
const BASE_CURRENCY_KEY = 'saas_base_currency';
const defaultCurrency = props.currencies.find(c => c.value === 'USD')?.value || props.currencies[0]?.value || 'USD';

onMounted(() => {
  const saved = localStorage.getItem(BASE_CURRENCY_KEY);
  if (saved && props.currencies.some(c => c.value === saved)) {
    filters.base_currency = saved;
    applyFilters();
  }
});
function onBaseCurrencyChange() {
  localStorage.setItem(BASE_CURRENCY_KEY, filters.base_currency);
  applyFilters();
}
// --- End base currency persistence ---

// Modal state
const showModal = ref(false);
const toDeleteId = ref(null);
const toDeleteName = ref('');

// Subscription Form state
const showFormModal = ref(false);
const formMode = ref('create');
const editSubscription = ref(null);

function askDelete(id, name) {
  toDeleteId.value = id;
  toDeleteName.value = name;
  showModal.value = true;
}
function confirmDelete() {
  router.delete(route('subscriptions.destroy', toDeleteId.value));
  showModal.value = false;
}
function cancelDelete() {
  showModal.value = false;
  toDeleteId.value = null;
  toDeleteName.value = '';
}
function applyFilters() {
  router.get(route('subscriptions.index'), filters, { preserveState: true, replace: true });
}
function resetFilters() {
  Object.keys(filters).forEach(k => filters[k] = '');
  filters.base_currency = localStorage.getItem(BASE_CURRENCY_KEY) || defaultCurrency;
  applyFilters();
}
function getRate(currency) {
  if (currency === 'USD') return 1.0;
  if (currency === 'EUR') return 1.08;
  if (currency === 'UAH') return 0.027;
  return 1.0;
}
function openAddModal() {
  formMode.value = 'create';
  editSubscription.value = null;
  showFormModal.value = true;
}
function openEditModal(sub) {
  formMode.value = 'edit';
  editSubscription.value = { ...sub };
  showFormModal.value = true;
}
function closeFormModal() {
  showFormModal.value = false;
  editSubscription.value = null;
}
function refreshList() {
  router.reload({ only: ['subscriptions', 'top', 'grouped', 'currencySummary', 'total30', 'total365'] });
}
function formatAmount(value) {
  if (typeof value !== 'number') value = Number(value);
  if (isNaN(value)) return '';
  return value.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).replace(/,/g, ' ');
}
</script> 