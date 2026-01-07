<script setup>
import { ref, computed, onMounted } from 'vue'
import TradeLayout from './TradeLayout.vue'
import axios from 'axios';
import { getEcho } from '../echo';

const pairs = [
  { label: 'BTC / USD', symbol: 'BTC' },
  { label: 'ETH / USD', symbol: 'ETH' }
]

const selectedPair = ref(pairs[0])

const symbol = computed(() => selectedPair.value.symbol)

const onSymbolChanged = async (newSymbol) => {
  console.log('Symbol changed:', newSymbol)

  // Fetch new data for the selected symbol
  // Example: API call (replace with your actual API)
  const data = await fetchOrderBook(newSymbol)
  await fetchTrades()
//   orderBook.value[newSymbol] = data.orderBook
//   trades.value = data.trades
//   openOrders.value = data.openOrders
}

const orderBook = ref({
  BTC: { buys: [], sells: [] },
  ETH: { buys: [], sells: [] },
});

const openOrders = ref({})

const trades = ref({})

const ORDER_STATUS_MAP = {
  1: 'Open',
  2: 'Filled',
  3: 'Cancelled',
};

const formatOrder = (order) => ({
  price: Number(order.price).toLocaleString(undefined, {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }),
  amount: Number(order.amount).toFixed(4),
  status: ORDER_STATUS_MAP[order.status] ?? 'Unknown',
  created_at: order.created_at ?? null,
  side: order.side ?? null,
  pair: order.pair ?? null,
  time: order.time ?? null,
  id: order.id ?? null,
})

const fetchOrderBook = async (pair) => {
  try {
    const token = localStorage.getItem('token');

    const res = await axios.get(`/api/orders?symbol=${pair}`, {
        headers: {
            Authorization: `Bearer ${token}`,
        },
    });
    const data = res.data;

    if (!orderBook.value[pair]) {
      orderBook.value[pair] = { buys: [], sells: [] };
    }

    orderBook.value[pair].buys = data.buy.map(formatOrder);
    orderBook.value[pair].sells = data.sell.map(formatOrder);
    console.log("dat", data)
    openOrders.value = data.openOrder.map(formatOrder)

  } catch (err) {
    console.error(err);
    orderBook.value[pair] = { buys: [], sells: [] };
  }
};

const fetchTrades = async () => {
  try {
    const token = localStorage.getItem('token');

    const res = await axios.get(`/api/trades`, {
        headers: {
            Authorization: `Bearer ${token}`,
        },
    });
    const data = res.data;

    console.log("dat", data)
    trades.value = data.data.map(formatOrder)

  } catch (err) {
    console.error(err);
    orderBook.value[pair] = { buys: [], sells: [] };
  }
};

const side = ref('buy');
const price = ref('');
const amount = ref('');
const loading = ref(false);

const isValid = computed(() => {
  return price.value > 0 && amount.value > 0;
});

const placeOrder = async () => {
  if (!isValid.value) {
    alert('Enter valid price and amount');
    return;
  }

  loading.value = true;

  try {
    const token = localStorage.getItem('token');

    await axios.post(
      '/api/orders',
      {
        symbol: symbol.value,
        side: side.value,
        price: price.value,
        amount: amount.value,
      },
      {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      }
    );

    price.value = '';
    amount.value = '';

    await fetchOrderBook(symbol.value);

  } catch (err) {
    alert(err.response?.data?.message ?? 'Order failed');
  } finally {
    loading.value = false;
  }
};

const cancelOrder = async (orderId) => {
  try {
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    const res = await fetch(`/api/orders/${orderId}/cancel`, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': token,
        Authorization: `Bearer ${localStorage.getItem('token')}`,
        Accept: 'application/json',   // MUST
        'Content-Type': 'application/json' // optional, safe to include
      },
    });

    if (!res.ok) {
      throw new Error('Cancel failed');
    }
    await fetchOrderBook(symbol.value);
  } catch (err) {
    console.error('Cancel order error:', err);
    alert('Unable to cancel order');
  }
};

onMounted(() => {
    fetchOrderBook(symbol.value)
    fetchTrades()

    const token = localStorage.getItem('token');
    const userId = localStorage.getItem('userId');

    if (token && userId) {
        const echo = getEcho(token);

        echo.private(`user.${userId}`)
            .listen('.OrderMatched', async (event) => {
                console.log('Order matched', event);
                await fetchOrderBook(symbol.value);
                await fetchTrades();
            });
    }
})
</script>

<template>
  <TradeLayout v-model="selectedPair" @symbol-changed="onSymbolChanged">
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
              {{ orderBook[selectedPair.symbol].sells[0]?.price ?? '-' }} USD
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
            <button
            class="flex-1 py-2 rounded font-medium"
            :class="side === 'buy'
                ? 'bg-green-600 text-white'
                : 'bg-green-100 text-green-700'"
            @click="side = 'buy'"
            >
            Buy {{ symbol }}
            </button>

            <button
            class="flex-1 py-2 rounded font-medium"
            :class="side === 'sell'
                ? 'bg-red-600 text-white'
                : 'bg-red-100 text-red-700'"
            @click="side = 'sell'"
            >
            Sell {{ symbol }}
            </button>
        </div>

        <div class="space-y-2">
            <input
            v-model.number="price"
            type="number"
            placeholder="Price (USD)"
            class="w-full border rounded px-3 py-2"
            />
            <input
            v-model.number="amount"
            type="number"
            placeholder="Amount"
            class="w-full border rounded px-3 py-2"
            />
        </div>

        <button
            class="w-full mt-4 py-2 rounded font-medium text-white"
            :class="side === 'buy' ? 'bg-green-600' : 'bg-red-600'"
            :disabled="!isValid || loading"
            @click="placeOrder"
        >
        {{ loading ? 'Placing...' : 'Place Order' }}
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
                <th class="text-right py-2">Status</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="s in orderBook[selectedPair.symbol].sells" :key="s.price">
                <td class="py-2">{{ s.price }}</td>
                <td class="py-2">{{ s.amount }}</td>
                <td class="text-right py-2">
                {{ s.status }}
                </td>
            </tr>
            </tbody>
        </table>

        <h3 class="text-green-600 font-medium mb-2">Buy Orders</h3>
        <table class="w-full text-sm border-collapse">
            <thead class="text-gray-500 border-b">
            <tr>
                <th class="text-left py-2">Price (USD)</th>
                <th class="text-left py-2">Amount</th>
                <th class="text-right py-2">Status</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="b in orderBook[selectedPair.symbol].buys" :key="b.price">
                <td class="py-2">{{ b.price }}</td>
                <td class="py-2">{{ b.amount }}</td>
                <td class="text-right py-2">
                {{ b.status }}
                </td>
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
                <th class="text-right py-2">Action</th>
            </tr>
            </thead>

            <tbody>
            <tr
                v-for="(o, i) in openOrders"
                :key="o.id ?? i"
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

                <!-- Cancel Button -->
                <td class="text-right py-2">
                <button
                    class="text-xs px-2 py-1 rounded border border-red-500 text-red-500 hover:bg-red-500 hover:text-white transition"
                    @click="cancelOrder(o.id)"
                >
                    Cancel
                </button>
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
