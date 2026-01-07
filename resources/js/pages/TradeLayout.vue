<script setup>
import { ref, watch, onMounted } from 'vue';
import axios from 'axios';
import { getEcho } from '../echo';

const pairs = [
  { label: 'BTC / USD', symbol: 'BTC' },
  { label: 'ETH / USD', symbol: 'ETH' },
];

const props = defineProps({
  modelValue: {
    type: Object,
    default: () => ({ label: 'BTC / USD', symbol: 'BTC' })
  }
});

const emit = defineEmits(['update:modelValue', 'symbol-changed']);
const selectedPair = ref(props.modelValue);

watch(selectedPair, (val) => {
  emit('update:modelValue', val);
  emit('symbol-changed', val.symbol);
});

const logout = () => {
  localStorage.removeItem('token');
  window.location.href = '/login';
};

const profile = ref({
  name: '',
  balanceUSD: 0,
  id: ''
});

const fetchProfile = async () => {
  try {
    const token = localStorage.getItem('token');
    if (!token) {
      logout();
      return;
    }

    const res = await axios.get('/api/profile', {
      headers: { Authorization: `Bearer ${token}` }
    });

    const user = res.data.data;

    profile.value.name = user.name;
    profile.value.balanceUSD = Number(user.balance).toFixed(2); // convert string to number
    profile.value.id = user.id
    localStorage.setItem('userId', profile.value.id)
  } catch (err) {
    console.error('Error fetching profile:', err);
    logout();
  }
};

onMounted(async () => {
    await fetchProfile();

    const token = localStorage.getItem('token');
    const userId = localStorage.getItem('userId');

    if (token && userId) {
        const echo = getEcho(token);

        echo.private(`user.${userId}`)
            .listen('.OrderMatched', async (event) => {
                console.log('Order matched', event);
                await fetchProfile();
            });
    }
});
</script>

<template>
  <div class="min-h-screen bg-[#f1f3f5] p-4">

    <div class="bg-white rounded-lg shadow px-6 py-4 mb-4 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">

      <div>
        <h1 class="text-lg font-semibold text-gray-800">Exchange Dashboard</h1>
        <p class="text-sm text-gray-500">Limit-Order Exchange Mini Engine</p>
        <p class="text-sm text-gray-600 mt-1">
          Welcome, <b>{{ profile.name }}</b> · Balance: <b>${{ profile.balanceUSD }}</b>
        </p>
      </div>

      <div class="flex items-center gap-2">
        <span class="text-sm text-gray-500">Trading Pair</span>
        <select v-model="selectedPair" class="border rounded px-3 py-2 text-sm font-medium">
          <option v-for="p in pairs" :key="p.symbol" :value="p">
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
