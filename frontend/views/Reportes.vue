<template>
  <div>
    <h2 class="mb-4">Reportes</h2>

    <div class="row mb-4">
      <div class="col-md-4">
        <div class="card">
          <div class="card-body">
            <label class="form-label">Desde</label>
            <input v-model="filtro.desde" type="date" class="form-control">
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card">
          <div class="card-body">
            <label class="form-label">Hasta</label>
            <input v-model="filtro.hasta" type="date" class="form-control">
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card">
          <div class="card-body">
            <label class="form-label">&nbsp;</label>
            <button @click="generarReporte" class="btn btn-primary w-100">
              <i class="fas fa-sync"></i> Generar Reporte
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header d-flex justify-content-between">
        <h5>Resumen de Ventas</h5>
        <button @click="exportarPDF" class="btn btn-sm btn-danger">
          <i class="fas fa-file-pdf"></i> Exportar PDF
        </button>
      </div>
      <div class="card-body">
        <div class="row mb-4">
          <div class="col-md-3">
            <div class="text-center">
              <h4>{{ reporte.totalVentas }}</h4>
              <p class="text-muted">Total Ventas</p>
            </div>
          </div>
          <div class="col-md-3">
            <div class="text-center">
              <h4>${{ reporte.montoTotal }}</h4>
              <p class="text-muted">Monto Total</p>
            </div>
          </div>
          <div class="col-md-3">
            <div class="text-center">
              <h4>${{ reporte.promedio }}</h4>
              <p class="text-muted">Promedio por Venta</p>
            </div>
          </div>
          <div class="col-md-3">
            <div class="text-center">
              <h4>{{ reporte.usuarios }}</h4>
              <p class="text-muted">Usuarios Activos</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Reportes',
  data() {
    return {
      filtro: {
        desde: new Date(new Date().setDate(new Date().getDate() - 30)).toISOString().split('T')[0],
        hasta: new Date().toISOString().split('T')[0]
      },
      reporte: {
        totalVentas: 0,
        montoTotal: '0.00',
        promedio: '0.00',
        usuarios: 0
      }
    }
  },
  mounted() {
    this.generarReporte()
  },
  methods: {
    generarReporte() {
      // Simular generación de reporte
      this.reporte = {
        totalVentas: 45,
        montoTotal: '12,500.00',
        promedio: '277.78',
        usuarios: 5
      }
    },
    exportarPDF() {
      alert('Función de exportación a PDF pendiente de implementar')
    }
  }
}
</script>
