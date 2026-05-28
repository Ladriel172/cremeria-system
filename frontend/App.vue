<template>
  <div id="app">
    <nav class="navbar navbar-expand-lg navbar-dark">
      <div class="container-fluid">
        <router-link to="/" class="navbar-brand">
          <i class="fas fa-ice-cream"></i> Cremeria Francis
        </router-link>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
          <ul class="navbar-nav ms-auto">
            <li class="nav-item" v-if="isLoggedIn">
              <span class="nav-link">{{ user.nombre }}</span>
            </li>
            <li class="nav-item" v-if="isLoggedIn">
              <button @click="logout" class="btn btn-outline-light btn-sm">
                <i class="fas fa-sign-out-alt"></i> Salir
              </button>
            </li>
          </ul>
        </div>
      </div>
    </nav>

    <div class="container-fluid">
      <div class="row">
        <!-- Sidebar -->
        <div class="col-md-2 sidebar" v-if="isLoggedIn">
          <div class="p-3">
            <h5>Menú</h5>
          </div>
          <router-link to="/dashboard" class="nav-link" :class="{ active: $route.name === 'Dashboard' }">
            <i class="fas fa-chart-line"></i> Dashboard
          </router-link>
          <router-link to="/ventas" class="nav-link" :class="{ active: $route.name === 'Ventas' }">
            <i class="fas fa-cash-register"></i> Ventas
          </router-link>
          <router-link to="/productos" class="nav-link" v-if="user.rol === 'admin'" :class="{ active: $route.name === 'Productos' }">
            <i class="fas fa-box"></i> Productos
          </router-link>
          <router-link to="/usuarios" class="nav-link" v-if="user.rol === 'admin'" :class="{ active: $route.name === 'Usuarios' }">
            <i class="fas fa-users"></i> Usuarios
          </router-link>
          <router-link to="/reportes" class="nav-link" v-if="user.rol === 'admin'" :class="{ active: $route.name === 'Reportes' }">
            <i class="fas fa-file-pdf"></i> Reportes
          </router-link>
        </div>

        <!-- Main Content -->
        <div :class="isLoggedIn ? 'col-md-10' : 'col-12'">
          <main class="main-content">
            <router-view />
          </main>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'App',
  data() {
    return {
      user: {},
      isLoggedIn: false
    }
  },
  mounted() {
    const token = localStorage.getItem('token')
    const user = localStorage.getItem('user')
    if (token && user) {
      this.isLoggedIn = true
      this.user = JSON.parse(user)
    }
  },
  methods: {
    logout() {
      localStorage.removeItem('token')
      localStorage.removeItem('user')
      this.$router.push('/login')
      this.isLoggedIn = false
    }
  }
}
</script>
