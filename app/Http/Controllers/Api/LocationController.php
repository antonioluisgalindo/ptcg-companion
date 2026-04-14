<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Province;
use App\Models\Locality;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function provinces()
    {
        return Province::orderBy('name')->get(['id', 'name']);
    }

    public function localities(Province $province)
    {
        return $province->localities()->orderBy('name')->get(['id', 'name']);
    }
}
