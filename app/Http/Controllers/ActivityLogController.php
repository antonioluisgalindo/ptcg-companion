<?php

namespace App\Http\Controllers;

use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Activity::class);
        $activities = Activity::with('causer', 'subject')->latest()->paginate(30);
        return view('activity-logs.index', compact('activities'));
    }
}
