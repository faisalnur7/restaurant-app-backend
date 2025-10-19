<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['table', 'user','items.product'])->where('payment_status','unpaid')
            ->withCount('items')
            ->latest();

        if ($request->has('order_type')) {
            $query->where('order_type', $request->order_type);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        return response()->json($query->paginate(10));
    }

    public function order_list(Request $request)
    {
        $query = Order::with(['table', 'user','items.product'])->where('payment_status','paid')
            ->withCount('items')
            ->latest()
            ->paginate(10);

        return response()->json($query);
    }

    public function recent(Request $request)
    {
        $query = Order::with(['table', 'user','items.product'])->where('payment_status','paid')
            ->withCount('items')
            ->latest()
            ->paginate(5);

        return response()->json($query);
    }


    public function kot(Request $request)
    {
        $query = Order::with(['table', 'user','items.product'])->where('payment_status','unpaid')->where('status','confirmed')
            ->withCount('items')
            ->latest()
            ->paginate(10);

        return response()->json($query);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'table_id' => 'nullable|exists:tables,id',
            'order_type' => 'required|in:dine_in,take_away,delivery',
            'subtotal' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'status' => 'nullable|in:pending,confirmed,preparing,served,completed,cancelled',
            'payment_status' => 'nullable|in:unpaid,paid,partial',
            'payment_method' => 'nullable|string|max:100',
            'items' => 'array|required',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        $validated['user_id'] = auth()->id();
        $order = Order::create($validated);

        // Save order items
        foreach ($validated['items'] as $item) {
            $data = $item;
            $data['total'] = $item['price'] * $item['quantity'];
            $order->items()->create($data);
        }

        // 🔑 Reload order with all relationships
        $order = Order::with(['items.product', 'table', 'user'])->find($order->id);

        return response()->json([
            'message' => 'Order created successfully',
            'order' => $order,
        ], 201);
    }



    /**
     * Display a single order with items.
     */
    public function show(Order $order)
    {
        return response()->json($order->load(['items.product', 'table', 'user']));
    }

    /**
     * Update order status or payment.
     */
    public function update(Request $request, $id)
    {
        $order = Order::with('items')->findOrFail($id);

        $validated = $request->validate([
            'table_id' => 'nullable|exists:tables,id',
            'subtotal' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'status' => 'nullable|in:pending,confirmed,preparing,served,completed,cancelled',
            'payment_status' => 'nullable|in:unpaid,paid,partial',
            'payment_method' => 'nullable|string|max:100',
            'items' => 'array|required',
            'items.*.id' => 'nullable|exists:order_items,id', // for existing items
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        // Update order data
        $order->update([
            'table_id' => $validated['table_id'] ?? null,
            'subtotal' => $validated['subtotal'],
            'discount' => $validated['discount'] ?? 0,
            'tax' => $validated['tax'] ?? 0,
            'total' => $validated['total'],
            'status' => $validated['status'] ?? $order->status,
            'payment_status' => $validated['payment_status'] ?? $order->payment_status,
            'payment_method' => $validated['payment_method'] ?? $order->payment_method,
        ]);

        // Sync order items
        $existingItemIds = $order->items->pluck('id')->toArray();
        $sentItemIds = collect($validated['items'])->pluck('id')->filter()->toArray();

        // Delete removed items
        $itemsToDelete = array_diff($existingItemIds, $sentItemIds);
        if (!empty($itemsToDelete)) {
            $order->items()->whereIn('id', $itemsToDelete)->delete();
        }

        // Update or create items
        foreach ($validated['items'] as $itemData) {
            $data = [
                'product_id' => $itemData['product_id'],
                'quantity' => $itemData['quantity'],
                'price' => $itemData['price'],
                'total' => $itemData['price'] * $itemData['quantity'],
            ];

            if (!empty($itemData['id'])) {
                // Update existing item
                $order->items()->where('id', $itemData['id'])->update($data);
            } else {
                // Create new item
                $order->items()->create($data);
            }
        }

        return response()->json([
            'message' => 'Order updated successfully',
            'order' => $order->load('items'),
        ], 200);
    }


    public function update_status(Request $request, Order $order)
    {
        $order->update($request->only(['status', 'payment_status']));
        return response()->json($order);
    }

    /**
     * Remove an order.
     */
    public function destroy(Order $order)
    {
        $order->delete();
        return response()->json(['message' => 'Order deleted']);
    }

    public function show_order(Order $order)
    {
        // Eager load relations to avoid N+1 problem
        $order->load('user', 'table', 'items.product');

        return view('orders.show', compact('order'));
    }
    
}
