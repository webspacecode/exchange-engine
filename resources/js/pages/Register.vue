<template>
  <div class="min-h-screen flex items-center justify-center bg-slate-100">
    <div class="bg-white p-8 rounded-xl shadow-md w-full max-w-md">
      <h1 class="text-2xl font-bold text-center mb-6">Register</h1>

      <form @submit.prevent="register" class="space-y-4">
        <input v-model="form.name" placeholder="Name" class="input" />
        <input v-model="form.email" placeholder="Email" class="input" />
        <input v-model="form.password" type="password" placeholder="Password" class="input" />

        <button class="btn-primary w-full">Create Account</button>
      </form>

      <p class="text-sm text-center mt-4">
        Already have an account?
        <router-link to="/login" class="text-indigo-600 font-medium">
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
});

const register = async () => {
  try {
    const res = await axios.post('/api/register', form);
    localStorage.setItem('token', res.data.token);
    window.location.href = '/dashboard';
  } catch (e) {
    alert('Registration failed');
  }
};
</script>
