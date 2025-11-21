<script setup lang="ts">
import { ref, onMounted, computed, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useProductsStore } from '@/stores/products'
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
import { Badge } from '@/components/ui/badge'
import { Skeleton } from '@/components/ui/skeleton'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Separator } from '@/components/ui/separator'
import { Alert, AlertDescription } from '@/components/ui/alert'
import { toast } from 'vue-sonner'
import { ArrowLeft, ShoppingCart, Package, Plus, Minus, Check, X, Info } from 'lucide-vue-next'

const route = useRoute()
const router = useRouter()
const productsStore = useProductsStore()
const cartStore = useCartStore()

const productId = computed(() => parseInt(route.params.id as string))
const quantity = ref(1)

// Use store data directly
const product = computed(() => productsStore.getProductById(productId.value))
const loading = computed(() => productsStore.loading)
const error = computed(() => productsStore.error)

// Check if product is in cart and get quantity
const cartItem = computed(() => cartStore.items.find((item) => item.product.id === productId.value))
const quantityInCart = computed(() => cartItem.value?.quantity || 0)
const isInCart = computed(() => quantityInCart.value > 0)

const isInStock = computed(() => product.value && product.value.stock > 0)
const maxQuantity = computed(() => product.value?.stock || 0)

// Initialize quantity from cart when product loads or cart changes
watch(
  [product, quantityInCart],
  () => {
    if (quantityInCart.value > 0) {
      quantity.value = quantityInCart.value
    }
  },
  { immediate: true },
)

const incrementQuantity = () => {
  if (quantity.value < maxQuantity.value) {
    quantity.value++
  }
}

const decrementQuantity = () => {
  if (quantity.value > 1) {
    quantity.value--
  }
}

const addToCart = () => {
  if (!product.value) return

  try {
    if (isInCart.value) {
      // Update quantity - calculate the difference
      const newQuantity = quantity.value
      const currentQuantity = quantityInCart.value
      const difference = newQuantity - currentQuantity

      if (difference > 0) {
        cartStore.addItem(product.value, difference)
        toast.success('Cart Updated', {
          description: `Updated to ${newQuantity} × ${product.value.name}`,
        })
      } else if (difference < 0) {
        cartStore.updateQuantity(product.value.id, newQuantity)
        toast.success('Cart Updated', {
          description: `Updated to ${newQuantity} × ${product.value.name}`,
        })
      } else {
        // toast.success('No Changes', {
        //   description: 'Quantity is the same as in cart'
        // })
      }
    } else {
      // Add new item
      cartStore.addItem(product.value, quantity.value)
      toast.success('Added to cart', {
        description: `${quantity.value} × ${product.value.name} added to your cart.`,
      })
    }
  } catch (err: any) {
    toast.error('Error', {
      description: err.message,
    })
  }
}

const removeFromCart = () => {
  if (!product.value) return

  cartStore.removeItem(product.value.id)
  quantity.value = 1
  toast.success('Removed from cart', {
    description: `${product.value.name} removed from your cart.`,
  })
}

const getStockStatus = () => {
  if (!product.value) return { text: 'Unknown', variant: 'secondary' }

  const stock = product.value.stock

  if (stock === 0) return { text: 'Out of Stock', variant: 'destructive' }
  if (stock < 10) return { text: `Only ${stock} left!`, variant: 'warning' }
  return { text: 'In Stock', variant: 'default' }
}

onMounted(async () => {
  // Only fetch if store is empty (e.g., direct navigation to this page)
  if (productsStore.products.length === 0) {
    try {
      await productsStore.fetchProducts()
    } catch (err) {
      toast.error('Error', {
        description: 'Failed to load product details.',
      })
    }
  }

  // If product still not found after fetching, show error
  if (!product.value && !loading.value) {
    toast.error('Error', {
      description: 'The product you are looking for does not exist.',
    })
  }
})
</script>

<template>
  <div class="container mx-auto px-4 py-8 max-w-6xl">
    <!-- Back Button -->
    <Button @click="router.push('/')" variant="ghost" size="sm" class="mb-6">
      <ArrowLeft class="w-4 h-4 mr-2" />
      Back to Products
    </Button>

    <!-- Loading State -->
    <div v-if="loading" class="grid md:grid-cols-2 gap-8">
      <div>
        <Skeleton class="w-full aspect-square rounded-lg" />
      </div>
      <div class="space-y-4">
        <Skeleton class="h-10 w-3/4" />
        <Skeleton class="h-6 w-1/4" />
        <Skeleton class="h-4 w-full" />
        <Skeleton class="h-4 w-full" />
        <Skeleton class="h-4 w-2/3" />
        <Skeleton class="h-12 w-full mt-8" />
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="error || !product" class="text-center py-16">
      <Package class="w-24 h-24 mx-auto text-gray-400 mb-4" />
      <h2 class="text-2xl font-semibold text-gray-700 mb-2">Product Not Found</h2>
      <p class="text-gray-600 mb-6">
        {{ error || 'The product you are looking for does not exist.' }}
      </p>
      <Button @click="router.push('/')"> Browse Products </Button>
    </div>

    <!-- Product Detail -->
    <div v-else class="grid md:grid-cols-2 gap-8">
      <!-- Product Image -->
      <div>
        <div class="aspect-square rounded-lg overflow-hidden bg-gray-100 sticky top-4">
          <img
            :src="product.image_url || 'https://via.placeholder.com/600'"
            :alt="product.name"
            class="w-full h-full object-cover"
          />
        </div>
      </div>

      <!-- Product Info -->
      <div>
        <div class="space-y-6">
          <!-- Title and Price -->
          <div>
            <h1 class="text-4xl font-bold text-gray-900 mb-2">{{ product.name }}</h1>
            <div class="flex items-center gap-3 mb-4">
              <span class="text-3xl font-bold text-gray-900">${{ product.price }}</span>
              <Badge :variant="getStockStatus().variant">
                {{ getStockStatus().text }}
              </Badge>
            </div>
          </div>

          <!-- In Cart Alert -->
          <Alert v-if="isInCart" class="border-blue-200 bg-blue-50">
            <Info class="h-4 w-4 text-blue-600" />
            <AlertDescription class="text-blue-800">
              Currently in cart:
              <strong>{{ quantityInCart }} {{ quantityInCart === 1 ? 'item' : 'items' }}</strong>
            </AlertDescription>
          </Alert>

          <Separator />

          <!-- Description -->
          <div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Description</h3>
            <p class="text-gray-700 leading-relaxed">{{ product.description }}</p>
          </div>

          <Separator />

          <!-- Product Details -->
          <div>
            <h3 class="text-lg font-semibold text-gray-900 mb-3">Product Details</h3>
            <dl class="space-y-2">
              <div class="flex justify-between">
                <dt class="text-gray-600">Product ID</dt>
                <dd class="font-medium text-gray-900">#{{ product.id }}</dd>
              </div>
              <div class="flex justify-between">
                <dt class="text-gray-600">Availability</dt>
                <dd class="font-medium">
                  <span v-if="isInStock" class="text-green-600 flex items-center gap-1">
                    <Check class="w-4 h-4" /> Available
                  </span>
                  <span v-else class="text-red-600 flex items-center gap-1">
                    <X class="w-4 h-4" /> Out of Stock
                  </span>
                </dd>
              </div>
              <div class="flex justify-between">
                <dt class="text-gray-600">Stock Quantity</dt>
                <dd class="font-medium text-gray-900">{{ product.stock }} units</dd>
              </div>
            </dl>
          </div>

          <Separator />

          <!-- Quantity Selector and Add to Cart -->
          <Card>
            <CardHeader>
              <CardTitle>{{ isInCart ? 'Update Cart' : 'Add to Cart' }}</CardTitle>
              <CardDescription>
                {{
                  isInCart
                    ? 'Adjust quantity in your cart'
                    : 'Select quantity and add to your shopping cart'
                }}
              </CardDescription>
            </CardHeader>
            <CardContent class="space-y-4">
              <!-- Quantity Controls -->
              <div class="space-y-2">
                <Label for="quantity">Quantity</Label>
                <div class="flex items-center gap-3">
                  <Button
                    @click="decrementQuantity"
                    variant="outline"
                    size="icon"
                    :disabled="!isInStock || quantity <= 1"
                  >
                    <Minus class="w-4 h-4" />
                  </Button>

                  <Input
                    id="quantity"
                    v-model.number="quantity"
                    type="number"
                    :min="1"
                    :max="maxQuantity"
                    :disabled="!isInStock"
                    class="w-24 text-center font-semibold"
                  />

                  <Button
                    @click="incrementQuantity"
                    variant="outline"
                    size="icon"
                    :disabled="!isInStock || quantity >= maxQuantity"
                  >
                    <Plus class="w-4 h-4" />
                  </Button>

                  <span class="text-sm text-gray-600 ml-2"> Max: {{ maxQuantity }} </span>
                </div>
              </div>

              <!-- Subtotal -->
              <div class="flex justify-between items-center text-lg font-semibold pt-2">
                <span class="text-gray-700">Subtotal:</span>
                <span class="text-gray-900"
                  >${{ (parseFloat(product.price) * quantity).toFixed(2) }}</span
                >
              </div>
            </CardContent>
            <CardFooter class="flex gap-3">
              <Button
                @click="addToCart"
                :disabled="!isInStock"
                class="flex-1"
                size="lg"
                :variant="isInCart && quantity === quantityInCart ? 'outline' : 'default'"
              >
                <ShoppingCart class="w-5 h-5 mr-2" />
                {{
                  isInStock
                    ? isInCart
                      ? quantity === quantityInCart
                        ? 'No Changes'
                        : 'Update Cart'
                      : 'Add to Cart'
                    : 'Out of Stock'
                }}
              </Button>

              <Button v-if="isInCart" @click="removeFromCart" variant="destructive" size="lg">
                Remove
              </Button>

              <Button @click="router.push('/cart')" variant="outline" size="lg"> View Cart </Button>
            </CardFooter>
          </Card>
        </div>
      </div>
    </div>
  </div>
</template>
