<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function index()
    {
        $logs = Activity::with('causer')->latest()->paginate(10);

        return Inertia::render('ActivityLogs/Index', [
            'logs' => $logs
        ]);
    }
}
