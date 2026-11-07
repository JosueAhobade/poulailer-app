<?php

namespace App\Models;

use Database\Factories\EggCollectionFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EggCollection extends Model
{
    /** @use HasFactory<EggCollectionFactory> */
    use HasFactory;

    protected $fillable = [
        'collection_date',
        'collected_count',
        'broken_count',
        'notes',
    ];

    protected $casts = [
        'collection_date' => 'date',
        'collected_count' => 'integer',
        'broken_count' => 'integer',
    ];

    public function saleItems(): HasMany
    {
        return $this->hasMany(EggSaleItem::class);
    }

    /**
     * Ajoute l'attribut `sold_count` (œufs déjà vendus de cette collecte).
     */
    #[Scope]
    protected function withSoldCount(Builder $query): void
    {
        $query->withSum('saleItems as sold_count', 'quantity');
    }

    /**
     * Collectes ayant encore des œufs disponibles, les plus anciennes d'abord.
     */
    #[Scope]
    protected function available(Builder $query): void
    {
        $query->withSoldCount()
            ->whereRaw(
                'collected_count - broken_count > (select coalesce(sum(quantity), 0) from egg_sale_items where egg_sale_items.egg_collection_id = egg_collections.id)'
            )
            ->orderBy('collection_date');
    }

    public function soldCount(): int
    {
        return (int) ($this->sold_count ?? $this->saleItems()->sum('quantity'));
    }

    public function sellableCount(): int
    {
        return $this->collected_count - $this->broken_count;
    }

    public function remainingCount(): int
    {
        return $this->sellableCount() - $this->soldCount();
    }
}
