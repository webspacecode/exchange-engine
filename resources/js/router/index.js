import { createRouter, createWebHistory } from 'vue-router';

import Login from '../pages/Login.vue';
import Register from '../pages/Register.vue';
import Dashboard from '../pages/Dashboard.vue';
import Trade from '../pages/Trade.vue';


const routes = [
  { path: '/', redirect: '/login' },

  {
    path: '/login',
    component: Login,
    meta: { guest: true },
  },
  {
    path: '/register',
    component: Register,
    meta: { guest: true },
  },
  {
    path: '/dashboard',
    component: Trade,
    meta: { auth: true },
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('token');

  if (to.meta.auth && !token) {
    return next('/login');
  }

  if (to.meta.guest && token) {
    return next('/dashboard');
  }

  next();
});

export default router;
