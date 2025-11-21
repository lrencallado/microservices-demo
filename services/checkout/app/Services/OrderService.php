<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class OrderService
{
    public function __construct(protected CatalogService $catalogService)
    {
    }

    /**
     * Create a new order with items.
     *
     * @param array $data
     * @return Order
     */
    public function createOrder(array $data): Order
    {
        DB::beginTransaction();
        try {
            // Validate products and calculate total
            $validatedItems = $this->validateAndPrepareItems($data['items']);
            $total = $this->calculateTotal($validatedItems);

            // Create order
            $order = Order::create([
                'email' => $data['email'],
                'total' => $total,
                'status' => 'pending',
            ]);

            $decrementedProducts = []; // Track successful decrements

            foreach ($validatedItems as $item) {
                $order->items()->create($item);

                try {
                    $this->catalogService->decrementStock($item['product_id'], $item['quantity']);
                    $decrementedProducts[] = $item; // Track success
                } catch (\Throwable $th) {
                    // Compensate: revert already decremented stock
                    foreach ($decrementedProducts as $decremented) {
                        try {
                            $this->catalogService->incrementStock($decremented['product_id'], $decremented['quantity']);
                        } catch (\Throwable $th) {
                            // Log compensation failure but continue reverting others
                            Log::error("Failed to compensate stock for product {$decremented['product_id']} with quantity {$decremented['quantity']}: " . $th->getMessage());
                        }
                    }
                    throw $th;
                }
            }

            // Mark order as completed
            $order->update(['status' => 'completed']);

            // Publish event to Redis
            $this->publishOrderCreatedEvent($order);

            DB::commit();

            return $order->load('items');
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * Validate products with Catalog API and prepare item data.
     *
     * @param array $items
     * @return array
     */
    private function validateAndPrepareItems(array $items): array
    {
        $preparedItems = [];
        foreach ($items as $item) {
            $product = $this->catalogService->validateProductStock($item['product_id'], $item['quantity']);
            $preparedItems[] = [
                'product_id' => $product['id'],
                'product_name' => $product['name'],
                'price' => $product['price'],
                'quantity' => $item['quantity'],
            ];
        }
        return $preparedItems;
    }

    /**
     * Calculated order total.
     *
     * @param array $items
     * @return float
     */
    private function calculateTotal(array $items): float
    {
        return collect($items)->sum(function ($item) {
            return $item['price'] * $item['quantity'];
        });
    }

    /**
     * Publish order created event to Redis.
     *
     * @param Order $order
     * @return void
     */
    private function publishOrderCreatedEvent(Order $order): void
    {
        $orderData = [
            'id' => $order->id,
            'email' => $order->email,
            'total' => $order->total,
            'items' => $order->items->map(function ($item) {
                return [
                    'name' => $item->product_name,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                ];
            })->toArray(),
            'created_at' => $order->created_at->toDateTimeString(),
        ];

        Redis::publish('orders:created', json_encode($orderData));
    }
}
