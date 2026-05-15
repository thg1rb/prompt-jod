<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FixedCategory extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'color',
        'icon',
        'sort_order',
        'type',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function customCategories(): HasMany
    {
        return $this->hasMany(Category::class, 'fixed_category_id');
    }

    public function scopeExpense($query)
    {
        return $query->where('type', 'expense');
    }

    public function scopeIncome($query)
    {
        return $query->where('type', 'income');
    }
}
