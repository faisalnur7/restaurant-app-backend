<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order #{{ $order->id }}</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.3.2/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 p-4">
    <div class="max-w-md mx-auto bg-white p-4 rounded shadow">
        <h2 class="text-xl font-bold text-center mb-2">Restaurant POS</h2>
        <p class="text-center text-sm mb-2">Order ID: #{{ $order->id }}</p>
        <p class="text-center text-xs text-gray-500 mb-4">
            Payment: {{ $order->payment_method ?? 'Cash' }} | Table: {{ $order->table->name ?? '-' }} | Cashier: {{ $order->user->name ?? '-' }}
        </p>
        <p class="text-center text-xs text-gray-500 mb-2">Time: {{ $order->created_at->format('d M Y, h:i A') }}</p>
        
        <table class="w-full border-collapse border border-gray-300 mb-4 text-sm">
            <thead>
                <tr class="border-b border-gray-300">
                    <th class="p-1 text-left">Item</th>
                    <th class="p-1 text-center">Qty</th>
                    <th class="p-1 text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                    <tr class="border-b border-gray-200">
                        <td class="p-1">{{ $item->product->name ?? 'Item' }}</td>
                        <td class="p-1 text-center">{{ $item->quantity }}</td>
                        <td class="p-1 text-right">{{ $item->total }} ৳</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="text-right mb-4 font-semibold text-gray-700">
            Total: {{ $order->total }} ৳
        </div>

        {{-- QR Code --}}
        <div class="flex justify-center">
            {!! DNS2D::getBarcodeHTML(route('orders.show', $order), 'QRCODE') !!}
        </div>
    </div>
</body>
</html>
