<?php

namespace App\Models\Concerns;

use App\Models\Property;
use App\Models\Scopes\PropertyScope;
use App\Support\CurrentProperty;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Add to every operational model that has a property_id column. */
trait BelongsToProperty
{
    public static function bootBelongsToProperty(): void
    {
        static::addGlobalScope(new PropertyScope);
        static::creating(function ($model) {
            $model->property_id ??= CurrentProperty::id();
        });
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }
}
