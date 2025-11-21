import { ref, computed } from 'vue'
import { defineStore } from 'pinia'
import { toast } from 'vue-sonner'

export interface Product {
  id: number
  name: string
  description: string
  price: string
  stock: number
  image_url: string | null
  created_at: string
  updated_at: string
}

export const useProductsStore = defineStore('products', () => {
  const products = ref<Product[]>([])
  const loading = ref(false)
  const error = ref<string | null>(null)

  const inStockProducts = computed(() => products.value.filter((p) => p.stock > 0))

  const outOfStockProducts = computed(() => products.value.filter((p) => p.stock === 0))

  const getProductById = computed(() => {
    return (id: number) => products.value.find((p) => p.id === id)
  })

  const totalProducts = computed(() => products.value.length)

  // Actions
  async function fetchProducts() {
    loading.value = true
    error.value = null

    try {
      const response = await fetch('http://localhost/api/catalog/products')
      const data = await response.json()

      if (data.success) {
        products.value = data.data
      } else {
        toast.error(data.message || 'Failed to fetch products')
        throw new Error(data.message || 'Failed to fetch products')
      }
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'An error occurred'
      toast.error(error.value)
      console.error('Failed to fetch products:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  async function fetchProductById(id: number) {
    loading.value = true
    error.value = null

    try {
      const response = await fetch(`http://localhost/api/catalog/products/${id}`)
      const data = await response.json()

      if (data.success) {
        return data.data
      } else {
        toast.error(data.message || 'Failed to fetch product')
        throw new Error(data.message || 'Failed to fetch product')
      }
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'An error occurred'
      console.error('Failed to fetch product:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  function updateProductStock(productId: number, newStock: number) {
    const product = products.value.find((p) => p.id === productId)
    if (product) {
      product.stock = newStock
    }
  }

  function decrementStock(productId: number, quantity: number = 1) {
    const product = products.value.find((p) => p.id === productId)
    if (product && product.stock >= quantity) {
      product.stock -= quantity
    }
  }

  function reset() {
    products.value = []
    loading.value = false
    error.value = null
  }

  return {
    // State
    products,
    loading,
    error,

    // Getters
    inStockProducts,
    outOfStockProducts,
    getProductById,
    totalProducts,

    // Actions
    fetchProducts,
    fetchProductById,
    updateProductStock,
    decrementStock,
    reset,
  }
})
