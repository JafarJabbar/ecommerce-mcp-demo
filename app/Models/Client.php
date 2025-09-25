<?php
// app/Models/Client.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'company_name', 'tax_number', 'billing_address',
        'shipping_address', 'client_type', 'credit_limit',
        'current_balance', 'payment_terms', 'is_active'
    ];

    protected $casts = [
        'billing_address' => 'array',
        'shipping_address' => 'array',
        'credit_limit' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeBusiness($query)
    {
        return $query->where('client_type', 'business');
    }

    public function scopeIndividual($query)
    {
        return $query->where('client_type', 'individual');
    }

    public function getAvailableCreditAttribute(): float
    {
        return $this->credit_limit - $this->current_balance;
    }
}
