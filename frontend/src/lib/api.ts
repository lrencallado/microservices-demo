const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || '/api'

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

export interface OrderItem {
  product_id: number
  quantity: number
}

export interface OrderItemResponse {
  id: number
  order_id: number
  product_id: number
  product_name: string
  quantity: number
  price: string
  created_at: string
  updated_at: string
}

export interface Order {
  id: number
  email: string
  total_amount: string
  status: string
  items?: OrderItemResponse[]
  created_at: string
  updated_at: string
}

export interface CreateOrderRequest {
  items: OrderItem[]
  email: string
}

export interface ApiResponse<T> {
  success: boolean
  message?: string
  data?: T
  errors?: Record<string, string[]>
}

export class ApiError extends Error {
  constructor(
    message: string,
    public statusCode?: number,
    public response?: any,
  ) {
    super(message)
    this.name = 'ApiError'
  }
}

// Helper function to handle API responses
async function handleResponse<T>(response: Response): Promise<ApiResponse<T>> {
  const data = await response.json()

  if (!response.ok) {
    throw new ApiError(data.message || 'An error occurred', response.status, data)
  }

  return data
}

// API Client
export const api = {
  catalog: {
    /**
     * Get all products
     */
    async getProducts(): Promise<ApiResponse<Product[]>> {
      const response = await fetch(`${API_BASE_URL}/catalog/products`)
      return handleResponse<Product[]>(response)
    },

    /**
     * Get single product by ID
     */
    async getProduct(id: number): Promise<ApiResponse<Product>> {
      const response = await fetch(`${API_BASE_URL}/catalog/products/${id}`)
      return handleResponse<Product>(response)
    },
  },

  checkout: {
    /**
     * Create a new order
     */
    async createOrder(orderData: CreateOrderRequest): Promise<ApiResponse<Order>> {
      const response = await fetch(`${API_BASE_URL}/checkout/orders`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(orderData),
      })
      return handleResponse<Order>(response)
    },
  },
}
