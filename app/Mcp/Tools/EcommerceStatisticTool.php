<?php

namespace App\Mcp\Tools;

use Laravel\Mcp\Server\Tool;
use App\Models\Order;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Category;
use Illuminate\Support\Facades\DB;

class EcommerceStatisticsTool extends Tool
{
    public function name(): string
    {
        return 'ecommerce_statistics';
    }

    public function description(): string
    {
        return 'E-commerce mağaza statistikaları və analitikası əldə etmək üçün tool. Satış, müştəri və məhsul məlumatlarını analiz edir.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'action' => [
                    'type' => 'string',
                    'enum' => [
                        'sales_summary',
                        'top_products',
                        'customer_stats',
                        'category_performance',
                        'monthly_revenue',
                        'order_trends',
                        'low_stock_products'
                    ],
                    'description' => 'Hansı statistika məlumatının lazım olduğunu seçin'
                ],
                'limit' => [
                    'type' => 'integer',
                    'default' => 10,
                    'description' => 'Nəticə sayı limiti (məsələn, top 10 məhsul)'
                ],
                'period' => [
                    'type' => 'string',
                    'enum' => ['week', 'month', '3months', '6months', 'year', 'all'],
                    'default' => 'month',
                    'description' => 'Analiz dövrü'
                ]
            ],
            'required' => ['action']
        ];
    }

    public function handle(array $args): array
    {
        $action = $args['action'];
        $limit = $args['limit'] ?? 10;
        $period = $args['period'] ?? 'month';

        // Dövrün tarix aralığını hesabla
        $dateRange = $this->getDateRange($period);

        switch ($action) {
            case 'sales_summary':
                return $this->getSalesSummary($dateRange);

            case 'top_products':
                return $this->getTopProducts($limit, $dateRange);

            case 'customer_stats':
                return $this->getCustomerStats($dateRange);

            case 'category_performance':
                return $this->getCategoryPerformance($dateRange);

            case 'monthly_revenue':
                return $this->getMonthlyRevenue();

            case 'order_trends':
                return $this->getOrderTrends($dateRange);

            case 'low_stock_products':
                return $this->getLowStockProducts($limit);

            default:
                return ['error' => 'Naməlum action parametri'];
        }
    }

    private function getDateRange(string $period): ?array
    {
        if ($period === 'all') {
            return null;
        }

        $now = now();
        $start = match($period) {
            'week' => $now->subWeek(),
            'month' => $now->subMonth(),
            '3months' => $now->subMonths(3),
            '6months' => $now->subMonths(6),
            'year' => $now->subYear(),
            default => $now->subMonth()
        };

        return [$start, now()];
    }

    private function getSalesSummary($dateRange): array
    {
        $query = Order::query();

        if ($dateRange) {
            $query->whereBetween('created_at', $dateRange);
        }

        $totalOrders = $query->count();
        $totalRevenue = $query->sum('total');
        $averageOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

        return [
            'total_orders' => $totalOrders,
            'total_revenue' => number_format($totalRevenue, 2),
            'average_order_value' => number_format($averageOrderValue, 2),
            'period' => $dateRange ? 'Son ' . $this->getPeriodText($dateRange) : 'Bütün zamanlar',
            'currency' => 'AZN' // və ya istədiyiniz valyuta
        ];
    }

    private function getTopProducts($limit, $dateRange): array
    {
        $query = DB::table('products')
            ->leftJoin('order_product', 'products.id', '=', 'order_product.product_id')
            ->leftJoin('orders', 'order_product.order_id', '=', 'orders.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->select(
                'products.id',
                'products.name',
                'products.price',
                'products.stock',
                'categories.name as category_name',
                DB::raw('COALESCE(SUM(order_product.quantity), 0) as total_sold'),
                DB::raw('COALESCE(SUM(order_product.price), 0) as total_revenue')
            )
            ->groupBy('products.id', 'products.name', 'products.price', 'products.stock', 'categories.name');

        if ($dateRange) {
            $query->whereBetween('orders.created_at', $dateRange);
        }

        $products = $query->orderBy('total_sold', 'desc')
            ->limit($limit)
            ->get();

        return [
            'top_products' => $products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'category' => $product->category_name,
                    'price' => number_format($product->price, 2),
                    'stock' => $product->stock,
                    'total_sold' => $product->total_sold,
                    'total_revenue' => number_format($product->total_revenue, 2)
                ];
            })->toArray()
        ];
    }

    private function getCustomerStats($dateRange): array
    {
        $totalCustomers = Customer::count();

        $orderQuery = Order::query();
        if ($dateRange) {
            $orderQuery->whereBetween('created_at', $dateRange);
        }

        $activeCustomers = $orderQuery->distinct('customer_id')->count('customer_id');
        $repeatCustomers = $orderQuery->select('customer_id')
            ->groupBy('customer_id')
            ->havingRaw('COUNT(*) > 1')
            ->get()
            ->count();

        $topCustomers = Order::select('customer_id')
            ->selectRaw('COUNT(*) as order_count, SUM(total) as total_spent')
            ->with('customer:id,name,email')
            ->when($dateRange, function($query) use ($dateRange) {
                $query->whereBetween('created_at', $dateRange);
            })
            ->groupBy('customer_id')
            ->orderBy('total_spent', 'desc')
            ->limit(5)
            ->get();

        return [
            'total_customers' => $totalCustomers,
            'active_customers' => $activeCustomers,
            'repeat_customers' => $repeatCustomers,
            'repeat_rate' => $activeCustomers > 0 ? round(($repeatCustomers / $activeCustomers) * 100, 2) : 0,
            'top_customers' => $topCustomers->map(function ($order) {
                return [
                    'customer_name' => $order->customer->name,
                    'email' => $order->customer->email,
                    'order_count' => $order->order_count,
                    'total_spent' => number_format($order->total_spent, 2)
                ];
            })->toArray()
        ];
    }

    private function getCategoryPerformance($dateRange): array
    {
        $query = DB::table('categories')
            ->leftJoin('products', 'categories.id', '=', 'products.category_id')
            ->leftJoin('order_product', 'products.id', '=', 'order_product.product_id')
            ->leftJoin('orders', 'order_product.order_id', '=', 'orders.id')
            ->select(
                'categories.id',
                'categories.name',
                DB::raw('COUNT(DISTINCT products.id) as product_count'),
                DB::raw('COALESCE(SUM(order_product.quantity), 0) as total_sold'),
                DB::raw('COALESCE(SUM(order_product.price), 0) as total_revenue')
            )
            ->groupBy('categories.id', 'categories.name');

        if ($dateRange) {
            $query->whereBetween('orders.created_at', $dateRange);
        }

        $categories = $query->orderBy('total_revenue', 'desc')->get();

        return [
            'category_performance' => $categories->map(function ($category) {
                return [
                    'category_name' => $category->name,
                    'product_count' => $category->product_count,
                    'total_sold' => $category->total_sold,
                    'total_revenue' => number_format($category->total_revenue, 2)
                ];
            })->toArray()
        ];
    }

    private function getMonthlyRevenue(): array
    {
        $monthlyData = Order::select(
            DB::raw('YEAR(created_at) as year'),
            DB::raw('MONTH(created_at) as month'),
            DB::raw('COUNT(*) as order_count'),
            DB::raw('SUM(total) as revenue')
        )
            ->where('created_at', '>=', now()->subYear())
            ->groupBy(DB::raw('YEAR(created_at)'), DB::raw('MONTH(created_at)'))
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        return [
            'monthly_revenue' => $monthlyData->map(function ($data) {
                return [
                    'period' => $data->year . '-' . sprintf('%02d', $data->month),
                    'order_count' => $data->order_count,
                    'revenue' => number_format($data->revenue, 2)
                ];
            })->toArray()
        ];
    }

    private function getOrderTrends($dateRange): array
    {
        $query = Order::query();

        if ($dateRange) {
            $query->whereBetween('created_at', $dateRange);
        }

        $dailyOrders = $query->select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as order_count'),
            DB::raw('SUM(total) as daily_revenue')
        )
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date', 'desc')
            ->limit(30)
            ->get();

        return [
            'daily_trends' => $dailyOrders->map(function ($day) {
                return [
                    'date' => $day->date,
                    'order_count' => $day->order_count,
                    'revenue' => number_format($day->daily_revenue, 2)
                ];
            })->toArray()
        ];
    }

    private function getLowStockProducts($limit): array
    {
        $lowStockThreshold = 10; // Bu rəqəmi tənzimləyə bilərsiniz

        $products = Product::with('category:id,name')
            ->where('stock', '<=', $lowStockThreshold)
            ->orderBy('stock', 'asc')
            ->limit($limit)
            ->get();

        return [
            'low_stock_threshold' => $lowStockThreshold,
            'low_stock_products' => $products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'category' => $product->category->name ?? 'N/A',
                    'current_stock' => $product->stock,
                    'price' => number_format($product->price, 2),
                    'status' => $product->stock == 0 ? 'Stokda yoxdur' : 'Az qalıb'
                ];
            })->toArray()
        ];
    }

    private function getPeriodText($dateRange): string
    {
        $days = $dateRange[0]->diffInDays($dateRange[1]);

        if ($days <= 7) return 'həftə';
        if ($days <= 31) return 'ay';
        if ($days <= 93) return '3 ay';
        if ($days <= 186) return '6 ay';
        if ($days <= 365) return 'il';

        return 'dövr';
    }
}
