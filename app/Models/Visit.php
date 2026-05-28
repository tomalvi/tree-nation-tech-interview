<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Customer;

class Visit extends Model
{
    protected $fillable = [
        'customer_id',
        'visited_at'
    ];

    protected $casts = [
        'visited_at' => 'datetime'
    ];

      public function visits(): BelongsTo
    {
        return $this->BelongsTo(Customer::class);
    }
}
