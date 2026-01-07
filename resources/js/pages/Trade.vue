<script setup>
import { ref, computed } from 'vue'
import TradeLayout from './TradeLayout.vue'

const pairs = ['BTC/USD', 'ETH/USD']
const selectedPair = ref('BTC/USD')

const symbol = computed(() => selectedPair.value.split('/')[0])

const orderBook = {
  'BTC/USD': {
    sells: [
      { price: 10300, amount: 0.8 },
      { price: 10250, amount: 1.0 },
      { price: 10220, amount: 0.5 },
    ],
    buys: [
      { price: 10200, amount: 1.2 },
      { price: 10180, amount: 1.5 },
    ],
  },
  'ETH/USD': {
    sells: [
      { price: 2650, amount: 5 },
      { price: 2620, amount: 3.2 },
    ],
    buys: [
      { price: 2600, amount: 4.1 },
      { price: 2580, amount: 6 },
    ],
  },
}

const openOrders = [
  { side: 'Sell', price: 10250, status: 'Open' },
  { side: 'Buy', price: 10180, status: 'Open' },
]

const trades = [
  { pair: 'BTC/USD', side: 'Buy', price: 10220, time: 'Just Now' },
  { pair: 'BTC/USD', side: 'Sell', price: 10200, time: '2 mins ago' },
  { pair: 'BTC/USD', side: 'Buy', price: 10150, time: '10 mins ago' },
]
</script>

<template>
  <TradeLayout>
    <template #header>
      <div
        class="bg-white rounded-lg shadow px-6 py-3 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4"
      >
        <div class="flex items-center gap-3 font-semibold text-lg">
          🪙
          <select
            v-model="selectedPair"
            class="border rounded px-3 py-1 text-sm font-medium"
          >
            <option v-for="p in pairs" :key="p">{{ p }}</option>
          </select>
        </div>

        <div class="flex flex-wrap gap-6 text-sm text-gray-600">
          <span>
            Last Price:
            <b class="text-green-600">
              {{ orderBook[selectedPair].sells[0]?.price ?? '-' }} USD
            </b>
          </span>
          <span>
            24h Change:
            <b class="text-green-600">+2.5%</b>
          </span>
          <span>
            24h Volume:
            <b>120.5 {{ symbol }}</b>
          </span>
        </div>
      </div>
    </template>

    <template #order>
      <h2 class="font-semibold mb-4">Place Order</h2>

      <div class="flex gap-2 mb-3">
        <button class="flex-1 bg-green-600 text-white py-2 rounded">
          Buy {{ symbol }}
        </button>
        <button class="flex-1 bg-red-500 text-white py-2 rounded">
          Sell {{ symbol }}
        </button>
      </div>

      <div class="space-y-2">
        <input
          type="number"
          placeholder="Price (USD)"
          class="w-full border rounded px-3 py-2"
        />
        <input
          type="number"
          placeholder="Amount"
          class="w-full border rounded px-3 py-2"
        />
      </div>

      <button
        class="w-full bg-green-600 text-white py-2 rounded mt-4 font-medium"
      >
        Place Order
      </button>
    </template>

    <template #orderbook>
      <h2 class="font-semibold mb-3">Order Book</h2>

      <h3 class="text-red-600 font-medium mb-2">Sell Orders</h3>
      <table class="w-full text-sm border-collapse mb-4">
        <thead class="text-gray-500 border-b">
          <tr>
            <th class="text-left py-2">Price (USD)</th>
            <th class="text-left py-2">Amount</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="s in orderBook[selectedPair].sells" :key="s.price">
            <td class="py-2">{{ s.price.toLocaleString() }}</td>
            <td class="py-2">{{ s.amount }}</td>
          </tr>
        </tbody>
      </table>

      <h3 class="text-green-600 font-medium mb-2">Buy Orders</h3>
      <table class="w-full text-sm border-collapse">
        <thead class="text-gray-500 border-b">
          <tr>
            <th class="text-left py-2">Price (USD)</th>
            <th class="text-left py-2">Amount</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="b in orderBook[selectedPair].buys" :key="b.price">
            <td class="py-2">{{ b.price.toLocaleString() }}</td>
            <td class="py-2">{{ b.amount }}</td>
          </tr>
        </tbody>
      </table>
    </template>

    <template #openOrders>
      <h2 class="font-semibold mb-3">Open Orders</h2>

      <table class="w-full text-sm border-collapse">
        <thead class="text-gray-500 border-b">
          <tr>
            <th class="text-left py-2">Side</th>
            <th class="text-right py-2">Price (USD)</th>
            <th class="text-right py-2">Status</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="(o, i) in openOrders"
            :key="i"
            class="border-b last:border-0"
          >
            <td
              class="py-2"
              :class="o.side === 'Buy' ? 'text-green-600' : 'text-red-500'"
            >
              {{ o.side }}
            </td>
            <td class="text-right py-2">
              {{ o.price.toLocaleString() }}
            </td>
            <td class="text-right py-2 text-green-600">
              {{ o.status }}
            </td>
          </tr>
        </tbody>
      </table>
    </template>

    <template #tradeHistory>
      <h2 class="font-semibold mb-3">Trade History</h2>

      <table class="w-full text-sm border-collapse">
        <thead class="text-gray-500 border-b">
          <tr>
            <th class="text-left py-2">Pair</th>
            <th class="text-left py-2">Side</th>
            <th class="text-right py-2">Price (USD)</th>
            <th class="text-right py-2">Time</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="(t, i) in trades"
            :key="i"
            class="border-b last:border-0"
          >
            <td class="py-2">{{ t.pair }}</td>
            <td
              class="py-2"
              :class="t.side === 'Buy' ? 'text-green-600' : 'text-red-500'"
            >
              {{ t.side }}
            </td>
            <td class="text-right py-2">
              {{ t.price.toLocaleString() }}
            </td>
            <td class="text-right py-2 text-gray-500">
              {{ t.time }}
            </td>
          </tr>
        </tbody>
      </table>
    </template>
  </TradeLayout>
</template>
