<?php
// app/MCP/EcommerceMCPServer.php
namespace App\MCP;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Order;
use App\Models\User;
use App\Models\Client;
use Anthropic\MCPServer\MCPServer;
use Anthropic\MCPServer\Tool;
use Anthropic\MCPServer\Resource;

class EcommerceMCPServer extends MCPServer
{
    public function __construct()
    {
        parent::__construct('ecommerce-server', '1.0.0');
        $this->registerTools();
        $this->registerResources();
    }

    private function registerTools()
    {
        // Product Tools
        $this->addTool(new Tool(
            name: 'get_products',
            description: 'Retrieve products with optional filtering',
            inputSchema: [
                'type' => 'object',
                'properties' => [
                    'category_id' => ['type' => 'integer'],
                    'brand_id' => ['type' => 'integer'],
                    'status' => ['type' => 'string', 'enum' => ['active', 'inactive', 'draft']],
                    'is_featured' => ['type' => 'boolean'],
                    'min_price' => ['type' => 'number'],
                    'max_price' => ['type' => 'number'],
                    'in_stock' => ['type' => 'boolean'],
                    'limit' => ['type' => 'integer', 'default' => 20],
                    'search' => ['type' => 'string']
                ]
            ],
            handler: [$this, 'getProducts']
        ));

        $this->addTool(new Tool(
            name: 'get_product',
            description: 'Get a single product by ID',
            inputSchema: [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer']
                ],
                'required' => ['id']
            ],
            handler: [$this, 'getProduct']
        ));

        $this->addTool(new Tool(
            name: 'update_product_stock',
            description: 'Update product stock quantity',
            inputSchema: [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer'],
                    'quantity' => ['type' => 'integer']
                ],
                'required' => ['id', 'quantity']
            ],
            handler: [$this, 'updateProductStock']
        ));

        // Order Tools
        $this->addTool(new Tool(
            name: 'get_orders',
            description: 'Retrieve orders with optional filtering',
            inputSchema: [
                'type' => 'object',
                'properties' => [
                    'user_id' => ['type' => 'integer'],
                    'status' => ['type' => 'string'],
                    'payment_status' => ['type' => 'string'],
                    'date_from' => ['type' => 'string', 'format' => 'date'],
                    'date_to' => ['type' => 'string', 'format' => 'date'],
                    'limit' => ['type' => 'integer', 'default' => 20]
                ]
            ],
            handler: [$this, 'getOrders']
        ));

        $this->addTool(new Tool(
            name: 'get_order',
            description: 'Get a single order with products',
            inputSchema: [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer']
                ],
                'required' => ['id']
            ],
            handler: [$this, 'getOrder']
        ));

        $this->addTool(new Tool(
            name: 'update_order_status',
            description: 'Update order status',
            inputSchema: [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer'],
                    'status' => ['type' => 'string', 'enum' => ['pending', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded']]
                ],
                'required' => ['id', 'status']
            ],
            handler: [$this, 'updateOrderStatus']
        ));

        // Analytics Tools
        $this->addTool(new Tool(
            name: 'get_sales_analytics',
            description: 'Get sales analytics for a date range',
            inputSchema: [
                'type' => 'object',
                'properties' => [
                    'date_from' => ['type' => 'string', 'format' => 'date'],
                    'date_to' => ['type' => 'string', 'format' => 'date'],
                    'group_by' => ['type' => 'string', 'enum' => ['day', 'week', 'month'], 'default' => 'day']
                ]
            ],
            handler: [$this, 'getSalesAnalytics']
        ));

        $this->addTool(new Tool(
            name: 'get_top_products',
            description: 'Get top-selling products',
            inputSchema: [
                'type' => 'object',
                'properties' => [
                    'limit' => ['type' => 'integer', 'default' => 10],
                    'date_from' => ['type' => 'string', 'format' => 'date'],
                    'date_to' => ['type' => 'string', 'format' => 'date']
                ]
            ],
            handler: [$this, 'getTopProducts']
        ));

        // Customer Tools
        $this->addTool(new Tool(
            name: 'get_customers',
            description: 'Retrieve customers with optional filtering',
            inputSchema: [
                'type' => 'object',
                'properties' => [
                    'is_active' => ['type' => 'boolean'],
                    'has_orders' => ['type' => 'boolean'],
                    'limit' => ['type' => 'integer', 'default' => 20],
                    'search' => ['type' => 'string']
                ]
            ],
            handler: [$this, 'getCustomers']
        ));
    }

    private function registerResources()
    {
        $this->addResource(new Resource(
            uri: 'ecommerce://products',
            name: 'Products',
            description: 'All products in the ecommerce system',
            mimeType: 'application/json'
        ));

        $this->addResource(new Resource(
            uri: 'ecommerce://categories',
            name: 'Categories',
            description: 'Product categories',
            mimeType: 'application/json'
        ));

        $this->addResource(new Resource(
            uri: 'ecommerce://orders',
            name: 'Orders',
            description: 'Customer orders',
            mimeType: 'application/json'
        ));
    }

    // Tool Handlers
    public function getProducts(array $args): array
    {
        $query = Product::with(['category', 'brand']);

        if (isset($args['category_id'])) {
            $query->where('category_id', $args['category_id']);
        }

        if (isset($args['brand_id'])) {
            $query->where('brand_id', $args['brand_id']);
        }

        if (isset($args['status'])) {
            $query->where('status', $args['status']);
        }

        if (isset($args['is_featured'])) {
            $query->where('is_featured', $args['is_featured']);
        }

        if (isset($args['min_price'])) {
            $query->where('price', '>=', $args['min_price']);
        }

        if (isset($args['max_price'])) {
            $query->where('price', '<=', $args['max_price']);
        }

        if (isset($args['in_stock']) && $args['in_stock']) {
            $query->where('stock_quantity', '>', 0);
        }

        if (isset($args['search'])) {
            $query->where(function($q) use ($args) {
                $q->where('name', 'like', '%' . $args['search'] . '%')
                    ->orWhere('description', 'like', '%' . $args['search'] . '%')
                    ->orWhere('sku', 'like', '%' . $args['search'] . '%');
            });
        }

        $limit = $args['limit'] ?? 20;
        $products = $query->limit($limit)->get();

        return [
            'products' => $products->toArray(),
            'total' => $products->count()
        ];
    }

    public function getProduct(array $args): array
    {
        $product = Product::with(['category', 'brand'])->find($args['id']);

        if (!$product) {
            throw new \Exception('Product not found');
        }

        return $product->toArray();
    }

    public function updateProductStock(array $args): array
    {
        $product = Product::find($args['id']);

        if (!$product) {
            throw new \Exception('Product not found');
        }

        $product->update(['stock_quantity' => $args['quantity']]);

        return [
            'success' => true,
            'product' => $product->fresh()->toArray()
        ];
    }

    public function getOrders(array $args): array
    {
        $query = Order::with(['user', 'orderProducts.product']);

        if (isset($args['user_id'])) {
            $query->where('user_id', $args['user_id']);
        }

        if (isset($args['status'])) {
            $query->where('status', $args['status']);
        }

        if (isset($args['payment_status'])) {
            $query->where('payment_status', $args['payment_status']);
        }

        if (isset($args['date_from'])) {
            $query->whereDate('created_at', '>=', $args['date_from']);
        }

        if (isset($args['date_to'])) {
            $query->whereDate('created_at', '<=', $args['date_to']);
        }

        $limit = $args['limit'] ?? 20;
        $orders = $query->orderBy('created_at', 'desc')->limit($limit)->get();

        return [
            'orders' => $orders->toArray(),
            'total' => $orders->count()
        ];
    }

    public function getOrder(array $args): array
    {
        $order = Order::with(['user', 'client', 'orderProducts.product'])->find($args['id']);

        if (!$order) {
            throw new \Exception('Order not found');
        }

        return $order->toArray();
    }

    public function updateOrderStatus(array $args): array
    {
        $order = Order::find($args['id']);

        if (!$order) {
            throw new \Exception('Order not found');
        }

        $updateData = ['status' => $args['status']];

        if ($args['status'] === 'shipped' && !$order->shipped_at) {
            $updateData['shipped_at'] = now();
        }

        if ($args['status'] === 'delivered' && !$order->delivered_at) {
            $updateData['delivered_at'] = now();
        }

        $order->update($updateData);

        return [
            'success' => true,
            'order' => $order->fresh()->toArray()
        ];
    }

    public function getSalesAnalytics(array $args): array
    {
        $dateFrom = $args['date_from'] ?? now()->subMonth()->format('Y-m-d');
        $dateTo = $args['date_to'] ?? now()->format('Y-m-d');
        $groupBy = $args['group_by'] ?? 'day';

        $query = Order::whereBetween('created_at', [$dateFrom, $dateTo])
            ->where('status', '!=', 'cancelled');

        $dateFormat = match($groupBy) {
            'day' => '%Y-%m-%d',
            'week' => '%Y-%u',
            'month' => '%Y-%m',
        };

        $analytics = $query
            ->selectRaw("DATE_FORMAT(created_at, '$dateFormat') as period")
            ->selectRaw('COUNT(*) as total_orders')
            ->selectRaw('SUM(total_amount) as total_revenue')
            ->selectRaw('AVG(total_amount) as average_order_value')
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        return [
            'analytics' => $analytics->toArray(),
            'summary' => [
                'total_orders' => $analytics->sum('total_orders'),
                'total_revenue' => $analytics->sum('total_revenue'),
                'average_order_value' => $analytics->avg('average_order_value')
            ]
        ];
    }

    public function getTopProducts(array $args): array
    {
        $limit = $args['limit'] ?? 10;
        $dateFrom = $args['date_from'] ?? null;
        $dateTo = $args['date_to'] ?? null;

        $query = Product::select('products.*')
            ->join('order_products', 'products.id', '=', 'order_products.product_id')
            ->join('orders', 'order_products.order_id', '=', 'orders.id')
            ->where('orders.status', '!=', 'cancelled')
            ->selectRaw('SUM(order_products.quantity) as total_sold')
            ->selectRaw('SUM(order_products.total_price) as total_revenue')
            ->groupBy('products.id');

        if ($dateFrom) {
            $query->whereDate('orders.created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('orders.created_at', '<=', $dateTo);
        }

        $products = $query->orderByDesc('total_sold')
            ->limit($limit)
            ->with(['category', 'brand'])
            ->get();

        return [
            'top_products' => $products->toArray()
        ];
    }

    public function getCustomers(array $args): array
    {
        $query = User::where('role', 'customer')->with(['client']);

        if (isset($args['is_active'])) {
            $query->where('is_active', $args['is_active']);
        }

        if (isset($args['has_orders']) && $args['has_orders']) {
            $query->has('orders');
        }

        if (isset($args['search'])) {
            $query->where(function($q) use ($args) {
                $q->where('first_name', 'like', '%' . $args['search'] . '%')
                    ->orWhere('last_name', 'like', '%' . $args['search'] . '%')
                    ->orWhere('email', 'like', '%' . $args['search'] . '%');
            });
        }

        $limit = $args['limit'] ?? 20;
        $customers = $query->limit($limit)->get();

        return [
            'customers' => $customers->toArray(),
            'total' => $customers->count()
        ];
    }
}
