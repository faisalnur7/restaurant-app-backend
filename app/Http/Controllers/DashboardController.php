<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{

    public function topProducts()
    {
        $topProducts = OrderItem::select('product_id', DB::raw('SUM(quantity) as sold'), DB::raw('SUM(total) as revenue'))
            ->with('product:id,name')
            ->groupBy('product_id')
            ->orderByDesc('sold')
            ->paginate(5); // <-- keep paginate only

        // Transform data for frontend
        $topProducts->getCollection()->transform(function($i) {
            return [
                'name' => $i->product->name,
                'sold' => $i->sold,
                'revenue' => $i->revenue,
            ];
        });

        return response()->json($topProducts);
    }


    public function overview()
    {
        $today = now()->toDateString();

        return response()->json([
            'stats' => [
                'totalSales' => Order::whereDate('created_at', $today)->sum('total'),
                'totalOrders' => Order::count(),
                'pendingOrders' => Order::where('status', 'pending')->count(),
                // 'totalCustomers' => Customer::count(),
            ],
            'salesChart' => Order::selectRaw('DATE(created_at) as date, SUM(total) as sales')
                ->whereBetween('created_at', [now()->subDays(6), now()])
                ->groupBy('date')
                ->orderBy('date')
                ->get(),
            'paymentBreakdown' => Order::select('payment_method', DB::raw('SUM(total) as value'))
                ->groupBy('payment_method')
                ->get()
                ->map(fn($i) => [
                    'method' => ucfirst($i->payment_method ?? 'Other'),
                    'value' => $i->value,
                ]),
        ]);
    }

}
