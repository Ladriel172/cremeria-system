<template>
  <div>
    <h2 class="mb-4">Dashboard</h2>

    <div class="row mb-4">
      <div class="col-md-3">
        <div class="card bg-primary text-white">
          <div class="card-body">
            <h5>Ventas Hoy</h5>
            <h3 v-if="stats">{{ stats.ventasHoy }}</h3>
            <p class="mb-0">{{ stats?.totalHoy || '$0' }}</p>
          </div>
        </div>
      </div>

      <div class="col-md-3">
        <div class="card bg-success text-white">
          <div class="card-body">
            <h5>Productos</h5>
            <h3 v-if="stats">{{ stats.totalProductos }}</h3>
            <p class="mb-0">En inventario</p>
          </div>
        </div>
      </div>

      <div class="col-md-3">
        <div class="card bg-warning text-white">
          <div class="card-body">
            <h5>Stock Bajo</h5>
            <h3 v-if="stats">{{ stats.stockBajo }}</h3>
            <p class="mb-0">Productos</p>
          </div>
        </div>
      </div>

      <div class="col-md-3">
        <div class="card bg-info text-white">
          <div class="card-body">
            <h5>Usuarios</h5>
            <h3 v-if="stats">{{ stats.totalUsuarios }}</h3>
            <p class="mb-0">Activos</p>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-md-8">
        <div class="card">
          <div class="card-header">
            <h5>Últimas Ventas</h5>
          </div>
          <div class="card-body">
            <div v-if="ventas.length === 0" class="text-center text-muted">
              No hay ventas registradas
            </div>
            <table v-else class="table table-hover">
              <thead>
                <tr>
                  <th>Folio</th>
                  <th>Total</th>
                  <th>Método</th>
                  <th>Estado</th>
                  <th>Fecha</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="venta in ventas" :key="venta.id">
                  <td>{{ venta.folio }}</td>
                  <td>${{ venta.total }}</td>
                  <td>{{ venta.metodo_pago }}</td>
                  <td>
                    <span class="badge" :class="getEstadoBadge(venta.estado)">
                      {{ venta.estado }}
                    </span>
                  </td>
                  <td>{{ formatDate(venta.created_at) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card">
          <div class="card-header">
            <h5>Información del Usuario</h5>
          </div>
          <div class="card-body">
            <p><strong>Nombre:</strong> {{ user.nombre }}</p>
            <p><strong>Correo:</strong> {{ user.correo }}</p>
            <p><strong>Rol:</strong> 
              <span class="badge" :class="user.rol === 'admin' ? 'bg-danger' : 'bg-info'">
                {{ user.rol }}
              </span>
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { saleService } from '../services/api.js'

export default {
  name: 'Dashboard',
  data() {
    return {
      user: {},
      ventas: [],
      stats: null
    }
  },
  mounted() {
    this.loadUser()
    this.loadVentas()
    this.calculateStats()
  },
  methods: {
    loadUser() {
      this.user = JSON.parse(localStorage.getItem('user') || '{}')
    },
    async loadVentas() {
      try {
        this.ventas = await saleService.list(0, 5)
      } catch (err) {
        console.error('Error loading sales:', err)
      }
    },
    calculateStats() {
      // Aquí se calcularían estadísticas reales desde el backend
      this.stats = {
        ventasHoy: 15,
        totalHoy: '$1,250.00',
        totalProductos: 45,
        stockBajo: 3,
        totalUsuarios: 5
      }
    },
    getEstadoBadge(estado) {
      const badges = {
        'completada': 'bg-success',
        'anulada': 'bg-danger',
        'pendiente': 'bg-warning'
      }
      return badges[estado] || 'bg-secondary'
    },
    formatDate(date) {
      return new Date(date).toLocaleDateString('es-ES')
    }
  }
}
</script>
