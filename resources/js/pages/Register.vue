<template>
  <div class="min-h-screen flex items-center justify-center bg-slate-100 px-4">
    <div class="bg-white p-8 rounded-xl shadow-md w-full max-w-md">
      <h1 class="text-2xl font-bold text-center mb-6">Register</h1>

      <form @submit.prevent="register" class="space-y-4">

        <div>
          <label class="block text-gray-600 text-sm mb-1">Name</label>
          <input
            v-model="form.name"
            type="text"
            placeholder="Your full name"
            class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-400"
          />
        </div>

        <div>
          <label class="block text-gray-600 text-sm mb-1">Email</label>
          <input
            v-model="form.email"
            type="email"
            placeholder="your@email.com"
            class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-400"
          />
        </div>

        <div>
          <label class="block text-gray-600 text-sm mb-1">Password</label>
          <input
            v-model="form.password"
            type="password"
            placeholder="Enter password"
            class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-400"
          />
        </div>

        <div>
          <label class="block text-gray-600 text-sm mb-1">Confirm Password</label>
          <input
            v-model="form.passwordConfirm"
            type="password"
            placeholder="Confirm password"
            class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-400"
          />
        </div>

        <button
          type="submit"
          class="w-full bg-indigo-600 text-white py-2 rounded font-medium hover:bg-indigo-700 transition"
        >
          Create Account
        </button>
      </form>

      <p class="text-sm text-center mt-4 text-gray-600">
        Already have an account?
        <router-link to="/login" class="text-indigo-600 font-medium hover:underline">
          Login
        </router-link>
      </p>
    </div>
  </div>
</template>

<script setup>
import { reactive } from 'vue';
import axios from 'axios';

const form = reactive({
  name: '',
  email: '',
  password: '',
  passwordConfirm: '',
});

const register = async () => {
  if (!form.name || !form.email || !form.password || !form.passwordConfirm) {
    alert('Please fill all fields');
    return;
  }

  if (form.password !== form.passwordConfirm) {
    alert('Passwords do not match');
    return;
  }

  try {
    const res = await axios.post('/api/register', {
      name: form.name,
      email: form.email,
      password: form.password,
      password_confirmation: form.passwordConfirm
    });

    localStorage.setItem('token', res.data.token);
    window.location.href = '/dashboard';
  } catch (e) {
    alert(e.response?.data?.message || 'Registration failed');
  }
};
</script>
