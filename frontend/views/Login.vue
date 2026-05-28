<template>
  <div class="login-container">
    <div class="row justify-content-center align-items-center min-vh-100">
      <div class="col-md-5">
        <div class="card shadow-lg border-0">
          <div class="card-body p-5">
            <div class="text-center mb-4">
              <i class="fas fa-ice-cream" style="font-size: 3rem; color: #8B4513;"></i>
              <h1 class="mt-3">Cremeria Francis</h1>
              <p class="text-muted">Sistema POS</p>
            </div>

            <form @submit.prevent="handleLogin">
              <div class="mb-3">
                <label class="form-label">Correo Electrónico</label>
                <input v-model="form.correo" type="email" class="form-control" required>
              </div>

              <div class="mb-3">
                <label class="form-label">Contraseña</label>
                <input v-model="form.password" type="password" class="form-control" required>
              </div>

              <button type="submit" class="btn btn-primary w-100" :disabled="loading">
                <span v-if="!loading">Iniciar Sesión</span>
                <span v-else>
                  <span class="spinner-border spinner-border-sm me-2"></span>
                  Cargando...
                </span>
              </button>
            </form>

            <div v-if="error" class="alert alert-danger mt-3">
              {{ error }}
            </div>

            <div class="text-center mt-4">
              <p class="text-muted small">Demo:</p>
              <p class="small">
                Admin: admin@cremeria.com / admin123<br>
                Vendedor: vendedor@cremeria.com / vendedor123
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { authService } from '../services/api.js'

export default {
  name: 'Login',
  data() {
    return {
      form: {
        correo: '',
        password: ''
      },
      loading: false,
      error: null
    }
  },
  methods: {
    async handleLogin() {
      try {
        this.loading = true
        this.error = null

        await authService.login(this.form.correo, this.form.password)
        this.$router.push('/dashboard')
      } catch (err) {
        this.error = err.message
      } finally {
        this.loading = false
      }
    }
  }
}
</script>

<style scoped>
.login-container {
  background: linear-gradient(135deg, #f5f5dc 0%, #deb887 100%);
  min-height: 100vh;
}

.card {
  border-radius: 12px;
}
</style>
