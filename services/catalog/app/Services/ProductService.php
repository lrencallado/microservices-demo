<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\DB;

class ProductService
{
    /**
     * Decrement product stock with pessimistic locking
     *
     * @param integer $id
     * @param integer $quantity
     * @return Product
     * @throws \Exception
     */
    public function decrementStock(int $id, int $quantity): Product
    {
        DB::beginTransaction();
        try {
            $product = Product::where('id', $id)
                ->lockForUpdate()
                ->first();

            if (!$product) {
                throw new \Exception('Product not found');
            }

            if ($product->stock < $quantity) {
                throw new \Exception(
                    "Insufficient stock. Available: {$product->stock}, Requested: {$quantity}"
                );
            }

            // Decrement stock
            $product->decrement('stock', $quantity);

            DB::commit();
            return $product;
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }

    /**
     * Increment product stock (for compensation/rollback)
     *
     * @param integer $id
     * @param integer $quantity
     * @return Product
     * @throws \Exception
     */
    public function incrementStock(int $id, int $quantity): Product
    {
        DB::beginTransaction();
        try {
            $product = Product::where('id', $id)
                ->lockForUpdate()
                ->first();

            if (!$product) {
                throw new \Exception('Product not found');
            }

            // Increment stock
            $product->stock = $product->stock + $quantity;
            $product->save();

            DB::commit();
            return $product;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
