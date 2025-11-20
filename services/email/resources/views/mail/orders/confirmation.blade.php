<x-mail::message>
# Order Confirmation

Hello,

Thank you for shopping with Cinch E-Commerce! Your order has been successfully placed.

<x-mail::panel>
## Order ID: #{{ $order['id'] }}

## Order Date: #{{ $order['created_at'] }}

## Email: #{{ $order['email'] }}
</x-mail::panel>

## Items:
<x-mail::table>
| Product       | Quantity      | Price         | Subtotal      |
| ------------- | :-----------: | ------------: | ------------: |
@foreach($order['items'] as $item)
| {{ $item['name'] }} | {{ $item['quantity'] }} | ${{ number_format($item['price'], 2) }} | ${{
number_format($item['price'] * $item['quantity'], 2) }} |
@endforeach
</x-mail::table>

## Total: {{ $order['total'] }}

We'll send you another email when your order ships.

If you have any questions, please don't hesitate to contact us.

Thanks,<br>
Cinch E-Commerce
</x-mail::message>
