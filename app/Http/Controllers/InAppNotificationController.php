<?php

namespace App\Http\Controllers;

use App\Models\InAppNotification;
use Illuminate\Http\Request;

class InAppNotificationController extends Controller
{
    public function markRead(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer',
        ]);

        $user = $request->user();
        if (!$user) {
            abort(403, 'No autorizado.');
        }

        InAppNotification::where('user_id', $user->id)
            ->whereIn('id', $validated['ids'])
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->noContent();
    }
}
