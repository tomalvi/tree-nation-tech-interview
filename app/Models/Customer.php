<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Visit;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Customer extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name',
        'trees_planted',
        'last_visit_at'
    ];

    protected $casts = [
        'last_visit_at' => 'datetime'
    ];

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class);
    }
}
