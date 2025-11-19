<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;

class CatalogService
{
    public string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('apiservices.catalog.base_url');
    }

    /**
     * Get product details from Catalog Service.
     *
     * @param integer $productId
     * @return array
     * @throws \Exception
     */
    public function getProduct(int $productId): array
    {
        try {
            $response = Http::get("{$this->baseUrl}/api/products/{$productId}");
            if (!$response->successful()) {
                throw new Exception("Product not found: {$productId}", 404);
            }
            return $response->json('data');
        } catch (\Throwable $th) {
            throw new \Exception("Failed to fetch product from Catalog service: {$th->getMessage()}", 500);
        }
    }

    /**
     * Validate product availability and stock.
     *
     * @param integer $productId
     * @param integer $requestedQuantity
     * @return array
     * @throws \Exception
     */
    public function validateProductStock(int $productId, int $requestedQuantity): array
    {
        $product = $this->getProduct($productId);

        if ($product['stock'] < $requestedQuantity) {
            throw new Exception(
                "Insufficient stock for product '{$product['name']}'. Available: {$product['stock']}, Requested: {$requestedQuantity}",
                400
            );
        }

        return $product;
    }
}
