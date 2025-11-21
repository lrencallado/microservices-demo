import { ref, computed } from 'vue'
import { defineStore } from 'pinia'
import type { Product } from '@/lib/api'

export interface CartItem {
  product: Product
  quantity: number
}

export const useCartStore = defineStore('cart', () => {
  // State
  const items = ref<CartItem[]>([])

  // Load cart from localStorage on initialization
  const loadCart = () => {
    const savedCart = localStorage.getItem('cart')
    if (savedCart) {
      try {
        items.value = JSON.parse(savedCart)
      } catch (error) {
        console.error('Failed to load cart: ', error)
        items.value = []
      }
    }
  }

  // Save cart to localStorage
  const saveCart = () => {
    localStorage.setItem('cart', JSON.stringify(items.value))
  }

  // Getters
  const itemCount = computed(() => items.value.reduce((total, item) => total + item.quantity, 0))

  const totalAmount = computed(() =>
    items.value.reduce((total, item) => {
      return total + parseFloat(item.product.price) * item.quantity
    }, 0),
  )

  const isEmpty = computed(() => items.value.length === 0)

  const getItemByProductId = computed(() => {
    return (productId: number) => items.value.find((item) => item.product.id === productId)
  })

  // Actions
  function addItem(product: Product, quantity: number = 1) {
    // Check if product is in stock
    if (product.stock === 0) {
      throw new Error('Product is out of stock')
    }

    const existingItem = items.value.find((item) => item.product.id === product.id)

    if (existingItem) {
      // Check if adding quantity exceeds stock
      if (existingItem.quantity + quantity > product.stock) {
        throw new Error('Not enough stock available')
      }
      existingItem.quantity += quantity
    } else {
      // Check if quantity exceeds stock
      if (quantity > product.stock) {
        throw new Error('Not enough stock available')
      }
      items.value.push({ product, quantity })
    }

    saveCart()
  }

  function removeItem(productId: number) {
    const index = items.value.findIndex((item) => item.product.id === productId)
    if (index !== -1) {
      items.value.splice(index, 1)
      saveCart()
    }
  }

  function updateQuantity(productId: number, quantity: number) {
    const item = items.value.find((item) => item.product.id === productId)

    if (!item) return

    // Validate quantity
    if (quantity <= 0) {
      removeItem(productId)
      return
    }

    // Check stock availability
    if (quantity > item.product.stock) {
      throw new Error('Not enough stock available')
    }

    item.quantity = quantity
    saveCart()
  }

  function incrementQuantity(productId: number) {
    const item = items.value.find((item) => item.product.id === productId)
    if (item) {
      updateQuantity(productId, item.quantity + 1)
    }
  }

  function decrementQuantity(productId: number) {
    const item = items.value.find((item) => item.product.id === productId)
    if (item) {
      updateQuantity(productId, item.quantity - 1)
    }
  }

  function clearCart() {
    items.value = []
    saveCart()
  }

  function getCheckoutPayload() {
    return {
      items: items.value.map((item) => ({
        product_id: item.product.id,
        quantity: item.quantity,
      })),
    }
  }

  // Initialize cart from localStorage
  loadCart()

  return {
    // State
    items,

    // Getters
    itemCount,
    totalAmount,
    isEmpty,
    getItemByProductId,

    // Actions
    addItem,
    removeItem,
    updateQuantity,
    incrementQuantity,
    decrementQuantity,
    clearCart,
    getCheckoutPayload,
    loadCart,
  }
})
