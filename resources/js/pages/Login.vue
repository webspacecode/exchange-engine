<template>
  <div class="min-h-screen flex items-center justify-center bg-slate-100">
    <div class="bg-white p-8 rounded-xl shadow-md w-full max-w-md">
      <h1 class="text-2xl font-bold text-center mb-6">Login</h1>

      <form @submit.prevent="login" class="space-y-4">
        <input
          v-model="form.email"
          type="email"
          placeholder="Email"
          class="w-full border rounded-lg px-4 py-2"
        />

        <input
          v-model="form.password"
          type="password"
          placeholder="Password"
          class="w-full border rounded-lg px-4 py-2"
        />

        <button
          class="w-full bg-indigo-600 text-white py-2 rounded-lg hover:bg-indigo-700"
        >
          Login
        </button>
      </form>

      <p class="text-sm text-center mt-4">
        Don’t have an account?
        <router-link to="/register" class="text-indigo-600 font-medium">
          Register
        </router-link>
      </p>
    </div>
  </div>
</template>

<script setup>
import { reactive } from 'vue';
import axios from 'axios';

const form = reactive({
  email: '',
  password: '',
});

const login = async () => {
  try {
    const res = await axios.post('/api/login', form);
    localStorage.setItem('token', res.data.token);
    window.location.href = '/dashboard';
  } catch (e) {
    alert('Invalid credentials');
  }
};
</script>
