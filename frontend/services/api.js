// API Service
// En desarrollo: http://localhost:8000
// En producción: URL de Render

const getAPIURL = () => {
  return 'https://cremeria-api.onrender.com'
}

const API_URL = getAPIURL()

console.log(`🚀 Conectando a API: ${API_URL}`)

const api = {
  async request(method, url, data = null) {
    const headers = {
      'Content-Type': 'application/json'
    }

    const token = localStorage.getItem('token')
    if (token) {
      headers['Authorization'] = `Bearer ${token}`
    }

    const options = {
      method,
      headers
    }

    if (data) {
      options.body = JSON.stringify(data)
    }

    try {
      const response = await fetch(`${API_URL}${url}`, options)

      if (response.status === 401) {
        localStorage.removeItem('token')
        localStorage.removeItem('user')
        window.location.href = '/login'
      }

      if (!response.ok) {
        const error = await response.json()
        throw new Error(error.detail || 'Error en la solicitud')
      }

      return await response.json()
    } catch (error) {
      console.error('API Error:', error)
      throw error
    }
  },

  get(url) {
    return this.request('GET', url)
  },

  post(url, data) {
    return this.request('POST', url, data)
  },

  put(url, data) {
    return this.request('PUT', url, data)
  },

  delete(url) {
    return this.request('DELETE', url)
  }
}

// Auth Service
export const authService = {
  async login(correo, password) {
    const data = await api.post('/api/auth/login', { correo, password })
    localStorage.setItem('token', data.access_token)
    localStorage.setItem('user', JSON.stringify(data.user))
    return data
  },

  async register(nombre, correo, password, rol = 'vendedor') {
    return api.post('/api/auth/register', { nombre, correo, password, rol })
  },

  logout() {
    localStorage.removeItem('token')
    localStorage.removeItem('user')
  },

  getCurrentUser() {
    return JSON.parse(localStorage.getItem('user') || '{}')
  }
}

// Products Service
export const productService = {
  async list(skip = 0, limit = 10, categoria = null) {
    let url = `/api/products/?skip=${skip}&limit=${limit}`
    if (categoria) url += `&categoria=${categoria}`
    return api.get(url)
  },

  async get(id) {
    return api.get(`/api/products/${id}`)
  },

  async create(product) {
    return api.post('/api/products/', product)
  },

  async update(id, product) {
    return api.put(`/api/products/${id}`, product)
  },

  async delete(id) {
    return api.delete(`/api/products/${id}`)
  },

  async lowStock() {
    return api.get('/api/products/stock/bajo')
  }
}

// Sales Service
export const saleService = {
  async list(skip = 0, limit = 10, usuario_id = null) {
    let url = `/api/sales/?skip=${skip}&limit=${limit}`
    if (usuario_id) url += `&usuario_id=${usuario_id}`
    return api.get(url)
  },

  async get(id) {
    return api.get(`/api/sales/${id}`)
  },

  async create(sale) {
    return api.post('/api/sales/', sale)
  },

  async cancel(id) {
    return api.put(`/api/sales/${id}/cancel`, {})
  }
}

export default api