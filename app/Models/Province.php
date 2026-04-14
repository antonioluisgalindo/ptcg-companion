<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Province extends Model
{
    protected $fillable = ['name'];

    public function localities(): HasMany
    {
        return $this->hasMany(Locality::class);
    }

    public function tournaments(): HasMany
    {
        return $this->hasMany(Tournament::class);
    }
}
