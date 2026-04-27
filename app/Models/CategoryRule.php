<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CategoryRule extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'category_id',
        'keyword',
        'priority',
        'is_active',
        'case_sensitive',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'priority' => 'integer',
            'is_active' => 'boolean',
            'case_sensitive' => 'boolean',
        ];
    }

    /**
     * Get the category that owns the rule.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Scope a query to only include active rules.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to order by priority (descending) then created_at.
     */
    public function scopeOrdered($query)
    {
        return $query->orderByDesc('priority')->orderBy('created_at');
    }

    /**
     * Check if the rule matches the given text.
     */
    public function matches(string $text): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $searchText = $this->case_sensitive ? $text : strtolower($text);
        $keyword = $this->case_sensitive ? $this->keyword : strtolower($this->keyword);

        return str_contains($searchText, $keyword);
    }
}
