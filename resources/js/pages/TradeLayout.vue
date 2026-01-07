<script setup>
import { ref, watch } from 'vue';

const pairs = [
  { label: 'BTC / USD', symbol: 'BTC' },
  { label: 'ETH / USD', symbol: 'ETH' },
];

const selectedPair = ref(pairs[0]);

const emit = defineEmits(['symbol-changed']);

const logout = () => {
  localStorage.removeItem('token');
  window.location.href = '/login';
};

watch(selectedPair, () => {
  emit('symbol-changed', selectedPair.value.symbol);
});
</script>

<template>
  <div class="min-h-screen bg-[#f1f3f5] p-4">

    <div class="bg-white rounded-lg shadow px-6 py-4 mb-4 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">

      <div>
        <h1 class="text-lg font-semibold text-gray-800">
          Exchange Dashboard
        </h1>
        <p class="text-sm text-gray-500">
          Limit-Order Exchange Mini Engine
        </p>
      </div>

      <div class="flex items-center gap-2">
        <span class="text-sm text-gray-500">Trading Pair</span>

        <select
          v-model="selectedPair"
          class="border rounded px-3 py-2 text-sm font-medium"
        >
          <option
            v-for="p in pairs"
            :key="p.symbol"
            :value="p"
          >
            {{ p.label }}
          </option>
        </select>
      </div>

      <div class="flex items-center gap-4">
        <div class="text-sm text-gray-500 text-right">
          Commission: <b>1.5%</b> · Matching: <b>Full Match Only</b>
        </div>
        <button
          @click="logout"
          class="bg-red-500 text-white px-4 py-2 rounded text-sm font-medium hover:bg-red-600"
        >
          Logout
        </button>
      </div>

    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">

      <div class="lg:col-span-3 bg-white rounded-lg shadow p-4">
        <slot name="order" />
      </div>

      <div class="lg:col-span-5 bg-white rounded-lg shadow p-4">
        <slot name="orderbook" />
      </div>

      <div class="lg:col-span-4 space-y-4">
        <div class="bg-white rounded-lg shadow p-4">
          <slot name="openOrders" />
        </div>

        <div class="bg-white rounded-lg shadow p-4">
          <slot name="tradeHistory" />
        </div>
      </div>

    </div>
  </div>
</template>
