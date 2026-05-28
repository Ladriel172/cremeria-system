<template>
  <div>
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2>Productos</h2>
      <button @click="showForm = !showForm" class="btn btn-primary">
        <i class="fas fa-plus"></i> Nuevo Producto
      </button>
    </div>

    <!-- Formulario -->
    <div v-if="showForm" class="card mb-4">
      <div class="card-body">
        <form @submit.prevent="saveProduct">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Nombre *</label>
              <input v-model="form.nombre" type="text" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Código de Barras</label>
              <input v-model="form.codigo_barras" type="text" class="form-control">
            </div>
          </div>

          <div class="row">
            <div class="col-md-4 mb-3">
              <label class="form-label">Precio *</label>
              <input v-model.number="form.precio" type="number" class="form-control" step="0.01" required>
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">Costo *</label>
              <input v-model.number="form.costo" type="number" class="form-control" step="0.01" required>
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">Stock *</label>
              <input v-model.number="form.stock" type="number" class="form-control" step="0.01" required>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Categoría</label>
              <input v-model="form.categoria" type="text" class="form-control">
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Unidad de Medida</label>
              <select v-model="form.tipo_medida" class="form-select">
                <option value="pieza">Pieza</option>
                <option value="gramos">Gramos</option>
                <option value="kg">Kg</option>
                <option value="litros">Litros</option>
                <option value="ml">ml</option>
              </select>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Descripción</label>
            <textarea v-model="form.descripcion" class="form-control" rows="3"></textarea>
          </div>

          <button type="submit" class="btn btn-success" :disabled="loading">
            {{ loading ? 'Guardando...' : 'Guardar Producto' }}
          </button>
          <button type="button" @click="showForm = false" class="btn btn-secondary ms-2">
            Cancelar
          </button>
        </form>
      </div>
    </div>

    <!-- Lista -->
    <div class="card">
      <div class="card-body">
        <div v-if="productos.length === 0" class="text-center text-muted">
          No hay productos registrados
        </div>
        <table v-else class="table table-hover">
          <thead>
            <tr>
              <th>Nombre</th>
              <th>Categoría</th>
              <th>Precio</th>
              <th>Stock</th>
              <th>Unidad</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="producto in productos" :key="producto.id">
              <td>{{ producto.nombre }}</td>
              <td>{{ producto.categoria || '-' }}</td>
              <td>${{ producto.precio }}</td>
              <td>
                <span :class="producto.stock <= producto.stock_minimo ? 'text-danger font-weight-bold' : ''">
                  {{ producto.stock }}
                </span>
              </td>
              <td>{{ producto.tipo_medida }}</td>
              <td>
                <button @click="editProduct(producto)" class="btn btn-sm btn-info me-1">
                  <i class="fas fa-edit"></i>
                </button>
                <button @click="deleteProduct(producto.id)" class="btn btn-sm btn-danger">
                  <i class="fas fa-trash"></i>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script>
import { productService } from '../services/api.js'

export default {
  name: 'Productos',
  data() {
    return {
      productos: [],
      showForm: false,
      loading: false,
      form: {
        nombre: '',
        codigo_barras: '',
        precio: 0,
        costo: 0,
        stock: 0,
        stock_minimo: 5,
        categoria: '',
        tipo_medida: 'pieza',
        descripcion: ''
      }
    }
  },
  mounted() {
    this.loadProductos()
  },
  methods: {
    async loadProductos() {
      try {
        this.productos = await productService.list()
      } catch (err) {
        console.error('Error loading products:', err)
        alert('Error al cargar productos')
      }
    },
    async saveProduct() {
      try {
        this.loading = true
        await productService.create(this.form)
        this.showForm = false
        this.resetForm()
        this.loadProductos()
        alert('Producto guardado exitosamente')
      } catch (err) {
        alert('Error: ' + err.message)
      } finally {
        this.loading = false
      }
    },
    editProduct(producto) {
      this.form = { ...producto }
      this.showForm = true
    },
    async deleteProduct(id) {
      if (confirm('¿Está seguro de que desea eliminar este producto?')) {
        try {
          await productService.delete(id)
          this.loadProductos()
          alert('Producto eliminado')
        } catch (err) {
          alert('Error: ' + err.message)
        }
      }
    },
    resetForm() {
      this.form = {
        nombre: '',
        codigo_barras: '',
        precio: 0,
        costo: 0,
        stock: 0,
        stock_minimo: 5,
        categoria: '',
        tipo_medida: 'pieza',
        descripcion: ''
      }
    }
  }
}
</script>
