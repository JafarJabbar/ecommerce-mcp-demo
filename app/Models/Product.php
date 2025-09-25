<?php
// app/Models/Product.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'description', 'short_description', 'sku',
        'price', 'compare_price', 'cost_price', 'stock_quantity',
        'min_quantity', 'track_quantity', 'images', 'weight',
        'dimensions', 'category_id', 'brand_id', 'status',
        'is_featured', 'attributes'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'compare_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'weight' => 'decimal:2',
        'track_quantity' => 'boolean',
        'is_featured' => 'boolean',
        'images' => 'array',
        'dimensions' => 'array',
        'attributes' => 'array',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function orderProducts(): HasMany
    {
        return $this->hasMany(OrderProduct::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeInStock($query)
    {
        return $query->where('stock_quantity', '>', 0);
    }

    public function isInStock(): bool
    {
        return !$this->track_quantity || $this->stock_quantity > 0;
    }

    public function getDiscountPercentageAttribute(): ?float
    {
        return null;
    }
}
