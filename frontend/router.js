import { createRouter, createWebHashHistory } from 'vue-router'
import Login from './views/Login.vue'
import Dashboard from './views/Dashboard.vue'
import Productos from './views/Productos.vue'
import Ventas from './views/Ventas.vue'
import Usuarios from './views/Usuarios.vue'
import Reportes from './views/Reportes.vue'

const routes = [
  {
    path: '/login',
    name: 'Login',
    component: Login,
    meta: { requiresAuth: false }
  },
  {
    path: '/dashboard',
    name: 'Dashboard',
    component: Dashboard,
    meta: { requiresAuth: true }
  },
  {
    path: '/productos',
    name: 'Productos',
    component: Productos,
    meta: { requiresAuth: true, role: 'admin' }
  },
  {
    path: '/ventas',
    name: 'Ventas',
    component: Ventas,
    meta: { requiresAuth: true }
  },
  {
    path: '/usuarios',
    name: 'Usuarios',
    component: Usuarios,
    meta: { requiresAuth: true, role: 'admin' }
  },
  {
    path: '/reportes',
    name: 'Reportes',
    component: Reportes,
    meta: { requiresAuth: true, role: 'admin' }
  },
  {
    path: '/',
    redirect: '/dashboard'
  }
]

// Usar hash mode para GitHub Pages
const router = createRouter({
 history: createWebHashHistory('/cremeria-system/'),
  routes
})

// Guard para rutas protegidas
router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('token')
  const user = JSON.parse(localStorage.getItem('user') || '{}')

  if (to.meta.requiresAuth && !token) {
    window.location.href = '/cremeria-system/#/login'
  } else if (to.meta.role && user.rol !== to.meta.role) {
    next('/dashboard')
  } else if (to.path === '/login' && token) {
    window.location.href = '/cremeria-system/#/dashboard'
  } else {
    next()
  }
})

export default router
