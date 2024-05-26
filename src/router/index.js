import { createRouter, createWebHistory, createWebHashHistory } from 'vue-router'
import Library from '@/components/Library.vue';
import NewBook from '@/components/NewBook.vue';
import Account from '@/components/Account.vue';

const router = createRouter({
  history: createWebHashHistory(),
  // history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'Biblioteca Personal',
      component: Library
    },
    {
      path: '/add',
      name: 'Nuevo Libro',
      component: NewBook
    },
    {
      path: '/account',
      name: 'Cuenta y Ajustes',
      component: Account
    },
    {
      path: '/:pathMatch(.*)*',
      name: 'Biblioteca Personal',
      component: Library
    },
  ]
})

export default router
