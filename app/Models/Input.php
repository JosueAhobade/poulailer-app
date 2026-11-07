<?php

namespace App\Models;

use App\Enums\InputCategory;
use Database\Factories\InputFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Input extends Model
{
    /** @use HasFactory<InputFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'unit',
        'alert_threshold',
        'notes',
    ];

    protected $casts = [
        'category' => InputCategory::class,
        'alert_threshold' => 'decimal:2',
    ];

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Ajoute l'attribut `stock` (somme signée des mouvements).
     */
    #[Scope]
    protected function withStock(Builder $query): void
    {
        $query->withSum('movements as stock', 'quantity');
    }

    #[Scope]
    protected function ofCategory(Builder $query, InputCategory $category): void
    {
        $query->where('category', $category);
    }

    public function currentStock(): float
    {
        return (float) ($this->stock ?? $this->movements()->sum('quantity'));
    }

    public function isLowStock(): bool
    {
        return $this->alert_threshold !== null
            && $this->currentStock() <= (float) $this->alert_threshold;
    }

    public function optionLabel(): string
    {
        return sprintf(
            '%s (stock : %s %s)',
            $this->name,
            number_format($this->currentStock(), 2, ',', ' '),
            $this->unit
        );
    }
}
