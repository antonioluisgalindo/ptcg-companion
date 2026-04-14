<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Locality extends Model
{
    protected $fillable = ['province_id', 'name'];

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    public function tournaments(): HasMany
    {
        return $this->hasMany(Tournament::class);
    }
}
