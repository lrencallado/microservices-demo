<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useCartStore } from '@/stores/cart'
import {
  Card,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Separator } from '@/components/ui/separator'
import { toast } from 'vue-sonner'
import { ShoppingCart, Trash2, Plus, Minus, ArrowLeft, CreditCard } from 'lucide-vue-next'

const router = useRouter()
const cartStore = useCartStore()

// Form data
const customerEmail = ref('')
const submitting = ref(false)

// Computed
const cartItems = computed(() => cartStore.items)
const itemCount = computed(() => cartStore.itemCount)
const totalAmount = computed(() => cartStore.totalAmount)
const isCartEmpty = computed(() => cartItems.value.length === 0)

// Methods
const updateQuantity = (productId: number, quantity: number) => {
  try {
    cartStore.updateQuantity(productId, quantity)
  } catch (err: any) {
    toast({
      title: 'Error',
      description: err.message,
      variant: 'destructive',
    })
  }
}

const removeItem = (productId: number) => {
  cartStore.removeItem(productId)
  toast.success('Item has been removed from your cart.')
}

const incrementQuantity = (productId: number, currentQuantity: number) => {
  updateQuantity(productId, currentQuantity + 1)
}

const decrementQuantity = (productId: number, currentQuantity: number) => {
  if (currentQuantity > 1) {
    updateQuantity(productId, currentQuantity - 1)
  }
}

const handleCheckout = async () => {
  if (!customerEmail.value.trim() || !isValidEmail(customerEmail.value)) {
    toast.error('Validation Error', {
      description: 'Please enter a valid email address',
    })
    return
  }

  if (isCartEmpty.value) {
    toast.error('Cart is empty.', {
      description: 'Please add items to your cart before checking out',
    })
    return
  }

  try {
    submitting.value = true

    const checkoutPayload = {
      ...cartStore.getCheckoutPayload(),
      email: customerEmail.value,
    }

    const response = await fetch('http://localhost/api/checkout/orders', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(checkoutPayload),
    })

    const data = await response.json()

    if (!response.ok || !data.success) {
      const errorMessage = data.message || 'Failed to place order'
      toast.error('Error', {
        description: errorMessage,
      })
      throw new Error(errorMessage)
    }

    // Success
    toast.success(`Order #${data.data.id} has been confirmed. Check your email for details`)

    // Clear cart and form
    cartStore.clearCart()
    customerEmail.value = ''

    // Redirect to products page after 2 seconds
    setTimeout(() => {
      router.push('/')
    }, 2000)
  } catch (err: any) {
    toast.error('Error', {
      description: err.message || 'Something went wrong. Please try again.',
    })
  } finally {
    submitting.value = false
  }
}

const isValidEmail = (email: string): boolean => {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  return emailRegex.test(email)
}
</script>

<template>
  <div class="container mx-auto px-4 py-8 max-w-7xl">
    <!-- Header -->
    <div class="flex items-center gap-4 mb-8">
      <div class="flex-1">
        <h1 class="text-4xl font-bold text-gray-900">Shopping Cart</h1>
        <p class="text-gray-600 mt-2">
          {{ itemCount }} item{{ itemCount !== 1 ? 's' : '' }} in your cart
        </p>
      </div>
      <Button @click="router.push('/')" variant="ghost" size="sm">
        <ArrowLeft class="w-4 h-4 mr-2" />
        Back to Products
      </Button>
    </div>

    <!-- Empty Cart State -->
    <div v-if="isCartEmpty" class="text-center py-16">
      <ShoppingCart class="w-24 h-24 mx-auto text-gray-400 mb-4" />
      <h2 class="text-2xl font-semibold text-gray-700 mb-2">Your cart is empty</h2>
      <p class="text-gray-600 mb-6">Add some products to get started</p>
      <Button @click="router.push('/')"> Browse Products </Button>
    </div>

    <!-- Cart Content -->
    <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Cart Items (Left Side) -->
      <div class="lg:col-span-2 space-y-4">
        <Card v-for="item in cartItems" :key="item.product.id">
          <CardContent class="p-6">
            <div class="flex gap-4">
              <!-- Product Image -->
              <div class="w-24 h-24 flex-shrink-0 rounded-md overflow-hidden bg-gray-100">
                <img
                  :src="item.product.image_url || 'https://via.placeholder.com/200'"
                  :alt="item.product.name"
                  class="w-full h-full object-cover"
                />
              </div>

              <!-- Product Details -->
              <div class="flex-1 min-w-0">
                <h3 class="text-lg font-semibold text-gray-900 mb-1">{{ item.product.name }}</h3>
                <p class="text-sm text-gray-600 line-clamp-2 mb-2">
                  {{ item.product.description }}
                </p>
                <p class="text-xl font-bold text-gray-900">${{ item.product.price }}</p>
              </div>

              <!-- Quantity Controls & Remove -->
              <div class="flex flex-col items-end justify-between">
                <!-- Quantity Controls -->
                <div class="flex items-center gap-2 border rounded-lg">
                  <Button
                    @click="decrementQuantity(item.product.id, item.quantity)"
                    variant="ghost"
                    size="sm"
                    :disabled="item.quantity <= 1"
                  >
                    <Minus class="w-4 h-4" />
                  </Button>
                  <span class="w-12 text-center font-semibold">{{ item.quantity }}</span>
                  <Button
                    @click="incrementQuantity(item.product.id, item.quantity)"
                    variant="ghost"
                    size="sm"
                    :disabled="item.quantity >= item.product.stock"
                  >
                    <Plus class="w-4 h-4" />
                  </Button>
                </div>

                <!-- Subtotal & Remove -->
                <div class="text-right">
                  <p class="text-lg font-bold text-gray-900 mb-2">
                    ${{ (parseFloat(item.product.price) * item.quantity).toFixed(2) }}
                  </p>
                  <Button
                    @click="removeItem(item.product.id)"
                    variant="ghost"
                    size="sm"
                    class="text-red-600 hover:text-red-700 hover:bg-red-50"
                  >
                    <Trash2 class="w-4 h-4 mr-1" />
                    Remove
                  </Button>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Checkout Summary (Right Side) -->
      <div class="lg:col-span-1">
        <Card class="sticky top-4">
          <CardHeader>
            <CardTitle>Order Summary</CardTitle>
            <CardDescription>Complete your purchase</CardDescription>
          </CardHeader>

          <CardContent class="space-y-4">
            <!-- Customer Information -->
            <div class="space-y-4">
              <div class="space-y-2">
                <Label for="email">Email Address</Label>
                <Input
                  id="email"
                  v-model="customerEmail"
                  type="email"
                  placeholder="john@example.com"
                  :disabled="submitting"
                />
              </div>
            </div>

            <Separator />

            <!-- Order Totals -->
            <div class="space-y-2">
              <div class="flex justify-between text-sm">
                <span class="text-gray-600">Subtotal</span>
                <span class="font-medium">${{ totalAmount.toFixed(2) }}</span>
              </div>
              <div class="flex justify-between text-sm">
                <span class="text-gray-600">Shipping</span>
                <span class="font-medium text-green-600">FREE</span>
              </div>
              <div class="flex justify-between text-sm">
                <span class="text-gray-600">Tax</span>
                <span class="font-medium">${{ (0).toFixed(2) }}</span>
              </div>

              <Separator />

              <div class="flex justify-between text-lg font-bold">
                <span>Total</span>
                <span>${{ totalAmount.toFixed(2) }}</span>
              </div>
            </div>
          </CardContent>

          <CardFooter>
            <Button
              @click="handleCheckout"
              :disabled="submitting || isCartEmpty"
              class="w-full"
              size="lg"
            >
              <CreditCard class="w-5 h-5 mr-2" />
              {{ submitting ? 'Processing...' : 'Place Order' }}
            </Button>
          </CardFooter>
        </Card>
      </div>
    </div>
  </div>
</template>
