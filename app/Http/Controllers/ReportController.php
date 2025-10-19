<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use PDF;

class ReportController extends Controller
{
    public function sales(Request $request)
    {
        $type = $request->query('type', 'daily'); // daily, monthly, yearly
        $start = $request->query('start');
        $end = $request->query('end');

        if ($start && $end) {
            // Use provided dates
            $start = Carbon::parse($start)->startOfDay();
            $end = Carbon::parse($end)->endOfDay();
        } else {
            // Determine start and end based on type
            $now = Carbon::now();

            switch ($type) {
                case 'daily':
                    $start = $now->copy()->startOfDay();
                    $end = $now->copy()->endOfDay();
                    break;

                case 'monthly':
                    $start = $now->copy()->startOfMonth();
                    $end = $now->copy()->endOfMonth();
                    break;

                case 'yearly':
                    $start = $now->copy()->startOfYear();
                    $end = $now->copy()->endOfYear();
                    break;

                default:
                    $start = $now->copy()->startOfDay();
                    $end = $now->copy()->endOfDay();
            }
        }

        $report = Order::whereBetween('created_at', [$start, $end])
                    ->select(
                        DB::raw('DATE(created_at) as date'),
                        DB::raw('COUNT(*) as total_orders'),
                        DB::raw('SUM(total) as total_revenue'),
                        DB::raw('SUM(CASE WHEN payment_method = "cash" THEN total ELSE 0 END) as cash'),
                        DB::raw('SUM(CASE WHEN payment_method IS NULL THEN total ELSE 0 END) as other')
                    )
                    ->groupBy('date')
                    ->orderBy('date', 'desc')
                    ->get();


        return response()->json($report);
    }


    // Order Report
    public function orders(Request $request)
    {
        $type = $request->query('type'); // daily, monthly, yearly
        $start = $request->query('start');
        $end = $request->query('end');

        $query = Order::with('items.product', 'table', 'user');

        if ($type === 'daily') {
            $query->whereDate('created_at', now());
        } elseif ($type === 'monthly') {
            $query->whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month);
        } elseif ($type === 'yearly') {
            $query->whereYear('created_at', now()->year);
        } elseif ($start && $end) {
            // Date range
            $query->whereBetween('created_at', [$start, $end]);
        }

        $orders = $query->orderBy('created_at', 'desc')->get();
        return response()->json($orders);
    }


    // Most Selling Products
    public function mostSellingProducts(Request $request)
    {
        $type = $request->query('type'); // daily, monthly, yearly
        $start = $request->query('start');
        $end = $request->query('end');

        $query = OrderItem::with('product');

        // If both start and end are provided, use them
        if ($start && $end) {
            $query->whereHas('order', function ($q) use ($start, $end) {
                $q->whereBetween('created_at', [$start, $end]);
            });
        } else {
            // Otherwise, filter based on type
            $query->whereHas('order', function ($q) use ($type) {
                switch ($type) {
                    case 'daily':
                        $q->whereDate('created_at', today());
                        break;

                    case 'monthly':
                        $q->whereYear('created_at', now()->year)
                        ->whereMonth('created_at', now()->month);
                        break;

                    case 'yearly':
                        $q->whereYear('created_at', now()->year);
                        break;

                    default:
                        // If no type and no range, fetch all
                }
            });
        }

        $products = $query
            ->select('product_id', DB::raw('SUM(quantity) as total_sold'))
            ->groupBy('product_id')
            ->orderByDesc('total_sold')
            ->get();

        return response()->json($products);
    }




    public function export(Request $request)
    {
        $format = $request->get('format', 'xlsx'); // Export format
        $type = $request->get('type'); // daily, monthly, yearly
        $start = $request->get('start');
        $end = $request->get('end');

        // Normalize Excel format
        if ($format === 'excel') $format = 'xlsx';

        // Determine date range and grouping
        switch ($type) {
            case 'daily':
                $start = $end = now()->toDateString();
                $groupFormat = '%Y-%m-%d';
                break;

            case 'monthly':
                $start = now()->startOfMonth()->toDateString();
                $end = now()->endOfMonth()->toDateString();
                $groupFormat = '%Y-%m';
                break;

            case 'yearly':
                $start = now()->startOfYear()->toDateString();
                $end = now()->endOfYear()->toDateString();
                $groupFormat = '%Y';
                break;

            default: // Custom range
                $start = $start ? date('Y-m-d', strtotime($start)) : now()->subDays(7)->toDateString();
                $end = $end ? date('Y-m-d', strtotime($end)) : now()->toDateString();
                $groupFormat = '%Y-%m-%d';
        }

        $selectLabel = "DATE_FORMAT(created_at, '$groupFormat') as date_label";

        // Build query
        $query = \App\Models\Order::query();

        if ($type === 'daily') {
            $query->whereDate('created_at', today());
        } elseif ($type === 'monthly') {
            $query->whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month);
        } elseif ($type === 'yearly') {
            $query->whereYear('created_at', now()->year);
        } else {
            $query->whereBetween('created_at', [$start, $end]);
        }

        $reports = $query->select(
                DB::raw($selectLabel),
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(total) as total_revenue'),
                DB::raw('SUM(CASE WHEN payment_method = "cash" THEN total ELSE 0 END) as cash'),
                DB::raw('SUM(CASE WHEN payment_method IS NULL THEN total ELSE 0 END) as other')
            )
            ->groupBy(DB::raw("DATE_FORMAT(created_at, '$groupFormat')"))
            ->orderBy('date_label', 'desc')
            ->get();

        // Format data for export
        $data = $reports->map(function ($item) use ($type) {
            $label = $item->date_label;

            if ($type === 'daily') {
                $label = \Carbon\Carbon::createFromFormat('Y-m-d', $label)->format('d M Y');
            } elseif ($type === 'monthly') {
                $label = \Carbon\Carbon::createFromFormat('Y-m', $label)->format('F Y');
            }
            // yearly: keep as year

            return [
                'Period' => $label,
                'Total Orders' => (int) $item->total_orders,
                'Total Revenue' => (float) $item->total_revenue,
                'Cash' => (float) $item->cash,
                'Other' => (float) $item->other,
            ];
        })->toArray();

        // Excel/CSV export
        if (in_array($format, ['csv', 'xlsx'])) {
            $fileName = "sales_report_{$type}_{$start}_to_{$end}.{$format}";
            return \Maatwebsite\Excel\Facades\Excel::download(
                new class($data) implements \Maatwebsite\Excel\Concerns\FromArray {
                    protected $data;
                    public function __construct($data) { $this->data = $data; }
                    public function array(): array { return $this->data; }
                },
                $fileName
            );
        }

        // PDF export
        if ($format === 'pdf') {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.sales-report', [
                'data' => $data,
                'start' => $start,
                'end' => $end,
                'type' => $type,
            ]);
            return $pdf->download("sales_report_{$type}_{$start}_to_{$end}.pdf");
        }

        return response()->json(['message' => 'Invalid format requested.'], 400);
    }

    public function order_export(Request $request)
    {
        $format = $request->get('format', 'xlsx'); // Export format
        $type = $request->get('type'); // daily, monthly, yearly
        $start = $request->get('start');
        $end = $request->get('end');

        // Normalize Excel format
        if ($format === 'excel') $format = 'xlsx';

        // Determine date range based on type or custom range
        if ($type === 'daily') {
            $start = $end = now()->toDateString();
        } elseif ($type === 'monthly') {
            $start = now()->startOfMonth()->toDateString();
            $end = now()->endOfMonth()->toDateString();
        } elseif ($type === 'yearly') {
            $start = now()->startOfYear()->toDateString();
            $end = now()->endOfYear()->toDateString();
        } else {
            $start = $start ? date('Y-m-d', strtotime($start)) : now()->subDays(7)->toDateString();
            $end = $end ? date('Y-m-d', strtotime($end)) : now()->toDateString();
        }

        // Build query
        $query = \App\Models\Order::with('items.product', 'user', 'table');

        if ($type === 'daily') {
            $query->whereDate('created_at', today());
        } elseif ($type === 'monthly') {
            $query->whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month);
        } elseif ($type === 'yearly') {
            $query->whereYear('created_at', now()->year);
        } else {
            $query->whereBetween('created_at', [$start, $end]);
        }

        $orders = $query->orderBy('created_at', 'desc')->get();

        // Format data for export
        $data = $orders->map(function ($order, $index) {
            return [
                '#' => $index + 1,
                'Order ID' => $order->id,
                'Date' => $order->created_at->format('d M Y H:i'),
                'Customer' => $order->user?->name ?? 'N/A',
                'Payment' => ucfirst($order->payment_method),
                'Type' => ucfirst(str_replace('_', ' ', $order->order_type)),
                'Total' => (float) $order->total,
                'Status' => ucfirst($order->status),
            ];
        })->toArray();

        // Excel/CSV export
        if (in_array($format, ['csv', 'xlsx'])) {
            $fileName = "orders_report_{$type}_{$start}_to_{$end}.{$format}";
            return \Maatwebsite\Excel\Facades\Excel::download(
                new class($data) implements \Maatwebsite\Excel\Concerns\FromArray {
                    protected $data;
                    public function __construct($data) { $this->data = $data; }
                    public function array(): array { return $this->data; }
                },
                $fileName
            );
        }

        // PDF export
        if ($format === 'pdf') {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.orders-report', [
                'data' => $data,
                'start' => $start,
                'end' => $end,
                'type' => $type,
                'companyName' => config('app.name'),
                'companyAddress' => config('app.address', 'Your Address Here'),
            ]);
            return $pdf->download("orders_report_{$type}_{$start}_to_{$end}.pdf");
        }

        return response()->json(['message' => 'Invalid format requested.'], 400);
    }


    public function most_selling_product_export(Request $request)
    {
        $format = $request->get('format', 'xlsx'); // pdf, csv, xlsx
        $type = $request->get('type');             // daily, monthly, yearly
        $start = $request->get('start');
        $end = $request->get('end');

        // Determine date range
        switch ($type) {
            case 'daily':
                $start = $end = now()->toDateString();
                break;
            case 'monthly':
                $start = now()->startOfMonth()->toDateString();
                $end = now()->endOfMonth()->toDateString();
                break;
            case 'yearly':
                $start = now()->startOfYear()->toDateString();
                $end = now()->endOfYear()->toDateString();
                break;
            default:
                $start = $start ?? now()->subWeek()->toDateString();
                $end = $end ?? now()->toDateString();
        }

        $query = OrderItem::with('product');

        $query->when($start && $end, function ($q) use ($start, $end) {
            $q->whereHas('order', function ($q2) use ($start, $end) {
                $q2->whereBetween('created_at', [$start, $end]);
            });
        });

        $products = $query
            ->select('product_id', DB::raw('SUM(quantity) as total_sold'), DB::raw('SUM(quantity * price) as total_revenue'))
            ->groupBy('product_id')
            ->orderByDesc('total_sold')
            ->get();

        $data = $products->map(function ($item, $index) {
            return [
                'Rank' => $index + 1,
                'Product' => $item->product->name ?? 'N/A',
                'Total Quantity Sold' => (int) $item->total_sold,
                'Total Revenue' => (float) $item->total_revenue,
            ];
        })->toArray();

        if (in_array($format, ['csv', 'xlsx'])) {
            return \Maatwebsite\Excel\Facades\Excel::download(
                new class($data) implements \Maatwebsite\Excel\Concerns\FromArray {
                    protected $data;
                    public function __construct($data) { $this->data = $data; }
                    public function array(): array { return $this->data; }
                },
                "most_selling_products_{$start}_to_{$end}.{$format}"
            );
        }

        if ($format === 'pdf') {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.most-selling-products', [
                'data' => $data,
                'start' => $start,
                'end' => $end,
                'type' => $type,
            ]);
            return $pdf->download("most_selling_products_{$start}_to_{$end}.pdf");
        }

        return response()->json(['message' => 'Invalid format requested'], 400);
    }



}
