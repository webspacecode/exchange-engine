<template>
  <div class="p-6">
    <h1 class="text-2xl font-bold">Dashboard</h1>

    <button
      class="mt-4 bg-red-500 text-white px-4 py-2 rounded"
      @click="logout"
    >
      Logout
    </button>
  </div>
</template>

<script setup>
const logout = () => {
  localStorage.removeItem('token');
  window.location.href = '/login';
};
</script>
