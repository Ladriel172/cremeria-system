<template>
  <div>
    <h2 class="mb-4">Gestión de Usuarios</h2>

    <div class="card">
      <div class="card-header">
        <h5>Usuarios del Sistema</h5>
      </div>
      <div class="card-body">
        <div v-if="usuarios.length === 0" class="text-center text-muted">
          No hay usuarios registrados
        </div>
        <table v-else class="table table-hover">
          <thead>
            <tr>
              <th>Nombre</th>
              <th>Correo</th>
              <th>Rol</th>
              <th>Estado</th>
              <th>Fecha Creación</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="usuario in usuarios" :key="usuario.id">
              <td>{{ usuario.nombre }}</td>
              <td>{{ usuario.correo }}</td>
              <td>
                <span class="badge" :class="usuario.rol === 'admin' ? 'bg-danger' : 'bg-info'">
                  {{ usuario.rol }}
                </span>
              </td>
              <td>
                <span class="badge" :class="usuario.estado === 'activo' ? 'bg-success' : 'bg-secondary'">
                  {{ usuario.estado }}
                </span>
              </td>
              <td>{{ formatDate(usuario.created_at) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Usuarios',
  data() {
    return {
      usuarios: []
    }
  },
  mounted() {
    this.loadUsuarios()
  },
  methods: {
    async loadUsuarios() {
      // Simular carga de usuarios
      this.usuarios = [
        {
          id: 1,
          nombre: 'Admin',
          correo: 'admin@cremeria.com',
          rol: 'admin',
          estado: 'activo',
          created_at: new Date().toISOString()
        },
        {
          id: 2,
          nombre: 'Vendedor',
          correo: 'vendedor@cremeria.com',
          rol: 'vendedor',
          estado: 'activo',
          created_at: new Date().toISOString()
        }
      ]
    },
    formatDate(date) {
      return new Date(date).toLocaleDateString('es-ES')
    }
  }
}
</script>
