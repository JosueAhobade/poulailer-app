<?php

namespace App\Models\Concerns;

use App\Enums\StockMovementType;
use App\Models\StockMovement;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Maintient automatiquement un mouvement de sortie de stock
 * synchronisé avec la consommation saisie sur le modèle.
 */
trait SyncsInputConsumption
{
    abstract protected function consumedInputId(): ?int;

    abstract protected function consumedQuantity(): ?float;

    abstract protected function consumptionDate(): CarbonInterface;

    abstract protected function consumptionLabel(): string;

    public static function bootSyncsInputConsumption(): void
    {
        static::saved(fn (self $model) => $model->syncInputConsumption());

        static::deleting(fn (self $model) => $model->stockMovements()->delete());
    }

    public function stockMovements(): MorphMany
    {
        return $this->morphMany(StockMovement::class, 'source');
    }

    public function syncInputConsumption(): void
    {
        $inputId = $this->consumedInputId();
        $quantity = $this->consumedQuantity();

        if ($inputId === null || $quantity === null || $quantity <= 0) {
            $this->stockMovements()->delete();

            return;
        }

        $movement = $this->stockMovements()->firstOrNew();

        $movement->fill([
            'input_id' => $inputId,
            'type' => StockMovementType::Consumption,
            'quantity' => -$quantity,
            'movement_date' => $this->consumptionDate(),
            'notes' => $this->consumptionLabel(),
        ])->save();
    }
}
