<?php

namespace App\Console\Commands;

use App\Mail\OrderConfirmation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redis;

class ListenForOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:listen';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Listen for new orders from Redis and send confirmation emails';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to listen for order events...');
        Redis::subscribe(['orders:created'], function ($message) {
            $this->info('Received order event: ' . $message);

            try {
                $orderData = json_decode($message, true);

                if (!$orderData || !isset($orderData['email'])) {
                    $this->error('Invalid order data received');
                    return;
                }

                // Send order confirmation email
                Mail::to($orderData['email'])->send(new OrderConfirmation($orderData));

                $this->info("Order confirmation email sent to: {$orderData['email']}");

            } catch (\Exception $e) {
                $this->error('Failed to send email: ' . $e->getMessage());
            }
        });
    }
}
