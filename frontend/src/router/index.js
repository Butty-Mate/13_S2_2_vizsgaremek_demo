import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const routes = [
  {
    path: '/',
    name: 'Home',
    component: () => import('../views/HomePage.vue')
  },
  {
    path: '/login',
    name: 'Login',
    component: () => import('../views/LoginPage.vue'),
    meta: { guest: true }
  },
  {
    path: '/register',
    name: 'Register',
    component: () => import('../views/RegisterPage.vue'),
    meta: { guest: true }
  },
  {
    path: '/campings',
    name: 'Campings',
    component: () => import('../views/CampingsPage.vue')
  },
  {
    path: '/campings/:id',
    name: 'CampingDetail',
    component: () => import('../views/CampingDetailPage.vue')
  },
  {
    path: '/my-bookings',
    name: 'MyBookings',
    component: () => import('../views/MyBookingsPage.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/owner/dashboard',
    name: 'OwnerDashboard',
    component: () => import('../views/OwnerDashboard.vue'),
    meta: { requiresAuth: true, requiresOwner: true }
  },
  {
    path: '/owner/campings/create',
    name: 'CreateCamping',
    component: () => import('../views/CreateCampingPage.vue'),
    meta: { requiresAuth: true, requiresOwner: true }
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

// Navigation guards
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore();

  // Guest only routes (login, register)
  if (to.meta.guest && authStore.isAuthenticated) {
    return next({ name: 'Home' });
  }

  // Protected routes
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    return next({ name: 'Login' });
  }

  // Owner only routes
  if (to.meta.requiresOwner && !authStore.isOwner) {
    return next({ name: 'Home' });
  }

  next();
});

export default router;
