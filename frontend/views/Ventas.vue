<template>
  <div>
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2>Nueva Venta</h2>
    </div>

    <div class="row">
      <div class="col-md-8">
        <div class="card">
          <div class="card-body">
            <h5>Carrito de Compra</h5>
            <div class="mb-3">
              <label class="form-label">Seleccionar Producto</label>
              <div class="input-group">
                <input v-model="searchProducto" @input="searchProducts" type="text" 
                       class="form-control" placeholder="Buscar producto...">
              </div>
              <div v-if="productosSearch.length > 0" class="list-group mt-2">
                <button v-for="prod in productosSearch" :key="prod.id" 
                        @click="addToCart(prod)" type="button" class="list-group-item list-group-item-action">
                  {{ prod.nombre }} - ${{ prod.precio }}
                </button>
              </div>
            </div>

            <table v-if="carrito.length > 0" class="table">
              <thead>
                <tr>
                  <th>Producto</th>
                  <th>Cantidad</th>
                  <th>Precio</th>
                  <th>Subtotal</th>
                  <th>Acción</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item, idx) in carrito" :key="idx">
                  <td>{{ item.nombre }}</td>
                  <td>
                    <input v-model.number="item.cantidad" type="number" class="form-control" 
                           style="width: 80px" @input="updateCarrito">
                  </td>
                  <td>${{ item.precio_unitario }}</td>
                  <td>${{ (item.cantidad * item.precio_unitario).toFixed(2) }}</td>
                  <td>
                    <button @click="removeFromCart(idx)" class="btn btn-sm btn-danger">
                      <i class="fas fa-trash"></i>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card">
          <div class="card-body">
            <h5>Resumen</h5>
            <div class="mb-2">
              <span>Subtotal:</span>
              <span class="float-end">${{ totales.subtotal.toFixed(2) }}</span>
            </div>
            <div class="mb-2">
              <span>Descuento:</span>
              <input v-model.number="totales.descuento" type="number" step="0.01" 
                     class="form-control form-control-sm float-end" style="width: 120px"
                     @input="updateCarrito">
            </div>
            <div class="mb-2">
              <span>IVA (19%):</span>
              <span class="float-end">${{ totales.iva.toFixed(2) }}</span>
            </div>
            <div class="mb-3 border-top pt-2">
              <strong>Total:</strong>
              <span class="float-end"><strong>${{ totales.total.toFixed(2) }}</strong></span>
            </div>

            <div class="mb-3">
              <label class="form-label">Método de Pago</label>
              <select v-model="venta.metodo_pago" class="form-select">
                <option value="efectivo">Efectivo</option>
                <option value="tarjeta">Tarjeta</option>
                <option value="transferencia">Transferencia</option>
                <option value="otro">Otro</option>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label">Monto Pagado</label>
              <input v-model.number="venta.monto_pagado" type="number" step="0.01" 
                     class="form-control" @input="updateCarrito">
            </div>

            <div v-if="venta.monto_pagado > 0" class="mb-3 p-2 bg-light rounded">
              <span>Cambio:</span>
              <span class="float-end">${{ venta.cambio.toFixed(2) }}</span>
            </div>

            <button @click="procesarVenta" class="btn btn-success w-100" :disabled="carrito.length === 0 || loading">
              {{ loading ? 'Procesando...' : 'Procesar Venta' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { productService, saleService } from '../services/api.js'

export default {
  name: 'Ventas',
  data() {
    return {
      searchProducto: '',
      productosSearch: [],
      carrito: [],
      venta: {
        metodo_pago: 'efectivo',
        monto_pagado: 0,
        cambio: 0
      },
      totales: {
        subtotal: 0,
        descuento: 0,
        iva: 0,
        total: 0
      },
      loading: false
    }
  },
  methods: {
    async searchProducts() {
      if (this.searchProducto.length < 2) {
        this.productosSearch = []
        return
      }
      try {
        const productos = await productService.list()
        this.productosSearch = productos.filter(p =>
          p.nombre.toLowerCase().includes(this.searchProducto.toLowerCase())
        )
      } catch (err) {
        console.error('Error searching products:', err)
      }
    },
    addToCart(producto) {
      const existente = this.carrito.find(item => item.producto_id === producto.id)
      if (existente) {
        existente.cantidad++
      } else {
        this.carrito.push({
          producto_id: producto.id,
          nombre: producto.nombre,
          cantidad: 1,
          precio_unitario: producto.precio
        })
      }
      this.searchProducto = ''
      this.productosSearch = []
      this.updateCarrito()
    },
    removeFromCart(idx) {
      this.carrito.splice(idx, 1)
      this.updateCarrito()
    },
    updateCarrito() {
      this.totales.subtotal = this.carrito.reduce((sum, item) =>
        sum + (item.cantidad * item.precio_unitario), 0
      )
      this.totales.iva = (this.totales.subtotal - this.totales.descuento) * 0.19
      this.totales.total = this.totales.subtotal - this.totales.descuento + this.totales.iva
      this.venta.cambio = this.venta.monto_pagado - this.totales.total
    },
    async procesarVenta() {
      if (this.carrito.length === 0) {
        alert('El carrito está vacío')
        return
      }

      try {
        this.loading = true
        const user = JSON.parse(localStorage.getItem('user'))
        const folio = 'V' + Date.now()

        const ventaData = {
          folio,
          usuario_id: user.id,
          subtotal: this.totales.subtotal,
          descuento: this.totales.descuento,
          iva: this.totales.iva,
          total: this.totales.total,
          metodo_pago: this.venta.metodo_pago,
          monto_pagado: this.venta.monto_pagado,
          cambio: this.venta.cambio,
          detalles: this.carrito
        }

        await saleService.create(ventaData)
        alert('Venta procesada exitosamente!\nFolio: ' + folio)
        this.resetVenta()
      } catch (err) {
        alert('Error: ' + err.message)
      } finally {
        this.loading = false
      }
    },
    resetVenta() {
      this.carrito = []
      this.venta = {
        metodo_pago: 'efectivo',
        monto_pagado: 0,
        cambio: 0
      }
      this.totales = {
        subtotal: 0,
        descuento: 0,
        iva: 0,
        total: 0
      }
    }
  }
}
</script>
