<script setup lang="ts">
import { onMounted, computed } from 'vue'
import { useCartStore } from '@/stores/cart'
import { useProductsStore } from '@/stores/products'
import type { Product } from '@/lib/api'
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
import { ShoppingCart, Package } from 'lucide-vue-next'
import { toast } from 'vue-sonner'
import { useRouter } from 'vue-router'

const router = useRouter()
const cartStore = useCartStore()
const productsStore = useProductsStore()

const products = computed(() => productsStore.products)
const loading = computed(() => productsStore.loading)
const error = computed(() => productsStore.error)

const addToCart = (product: Product) => {
  try {
    cartStore.addItem(product, 1)
    toast.success('Added to cart', {
      description: `1 × ${product.name} added to your cart.`,
    })
  } catch (error: any) {
    toast.error(error.message)
  }
}

const isInStock = (stock: number) => stock > 0

const getStockBadgeVariant = (
  stock: number,
): 'default' | 'destructive' | 'outline' | 'secondary' => {
  if (stock === 0) return 'destructive'
  if (stock < 10) return 'destructive'
  return 'default'
}

onMounted(async () => {
  await productsStore.fetchProducts()
})
</script>

<template>
  <div class="container mx-auto px-4 py-8">
    <div class="flex justify-between">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-4xl font-bold tracking-tight mb-2">Catalog</h1>
        <p class="text-muted-foreground">Browse our collection of amazing products</p>
      </div>

      <!-- Cart Icon with Badge -->
      <div class="relative">
        <Button @click="$router.push('/cart')" variant="outline" size="lg" class="relative">
          <ShoppingCart class="w-6 h-6" />
          <span class="ml-2">Cart</span>

          <!-- Badge showing item count -->
          <Badge
            v-if="cartStore.itemCount > 0"
            class="absolute -top-2 -right-2 h-6 w-6 flex items-center justify-center p-0 rounded-full"
            variant="destructive"
          >
            {{ cartStore.itemCount }}
          </Badge>
        </Button>
      </div>
    </div>

    <!-- Loading Skeletons -->
    <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
      <Card v-for="n in 8" :key="n">
        <CardHeader>
          <Skeleton class="h-48 w-full mb-4" />
          <Skeleton class="h-4 w-3/4 mb-2" />
          <Skeleton class="h-3 w-1/2" />
        </CardHeader>
        <CardFooter>
          <Skeleton class="h-10 w-full" />
        </CardFooter>
      </Card>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="text-center py-12">
      <Package class="mx-auto h-12 w-12 text-muted-foreground mb-4" />
      <h3 class="text-lg font-semibold mb-2">Failed to load products</h3>
      <p class="text-muted-foreground mb-4">{{ error }}</p>
      <Button @click="productsStore.fetchProducts">Try Again</Button>
    </div>

    <!-- Products Grid -->
    <div
      v-else-if="products.length > 0"
      class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6"
    >
      <Card
        v-for="product in products"
        :key="product.id"
        class="flex flex-col cursor-pointer hover:shadow-lg transition-shadow"
      >
        <CardHeader @click="router.push(`/products/${product.id}`)">
          <!-- Product Image -->
          <div class="aspect-square relative overflow-hidden rounded-lg bg-muted mb-4">
            <img
              :src="
                product.image_url ||
                'https://placehold.co/400x400/e2e8f0/64748b?text=' +
                  encodeURIComponent(product.name)
              "
              :alt="product.name"
              class="object-cover w-full h-full transition-transform hover:scale-105"
            />
            <!-- Stock Badge -->
            <Badge :variant="getStockBadgeVariant(product.stock)" class="absolute top-2 right-2">
              {{ product.stock === 0 ? 'Out of Stock' : `${product.stock} in stock` }}
            </Badge>
          </div>

          <CardTitle class="line-clamp-2">{{ product.name }}</CardTitle>
          <CardDescription class="line-clamp-2">
            {{ product.description }}
          </CardDescription>
        </CardHeader>

        <CardContent class="flex-grow">
          <p class="text-2xl font-bold">${{ parseFloat(product.price).toFixed(2) }}</p>
        </CardContent>

        <CardFooter>
          <Button @click="addToCart(product)" :disabled="!isInStock(product.stock)" class="w-full">
            <ShoppingCart class="mr-2 h-4 w-4" />
            {{ isInStock(product.stock) ? 'Add to Cart' : 'Out of Stock' }}
          </Button>
        </CardFooter>
      </Card>
    </div>

    <!-- Empty State -->
    <div v-else class="text-center py-12">
      <Package class="mx-auto h-12 w-12 text-muted-foreground mb-4" />
      <h3 class="text-lg font-semibold mb-2">No products available</h3>
      <p class="text-muted-foreground">Check back later for new items!</p>
    </div>
  </div>
</template>
