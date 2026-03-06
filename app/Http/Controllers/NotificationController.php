<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();

        // Only super_admin and capitania can see notifications
        if (!in_array($user->role, ['super_admin', 'capitania'], true)) {
            return response()->json(['notifications' => [], 'unread_count' => 0]);
        }

        $limit = min((int) $request->input('limit', 20), 50);

        $notifications = Notification::query()
            ->with(['user', 'firefighter', 'guardia'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($n) {
                return [
                    'id' => $n->id,
                    'type' => $n->type,
                    'title' => $n->title,
                    'message' => $n->message,
                    'read' => $n->isRead(),
                    'read_at' => $n->read_at?->toIso8601String(),
                    'created_at' => $n->created_at->toIso8601String(),
                    'user_name' => $n->user?->name,
                    'firefighter_name' => $n->firefighter ? "{$n->firefighter->nombres} {$n->firefighter->apellido_paterno}" : null,
                    'guardia_name' => $n->guardia?->name,
                    'metadata' => $n->metadata,
                ];
            });

        $unreadCount = Notification::unread()->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    public function markAsRead(Request $request, int $id): JsonResponse
    {
        $user = auth()->user();

        if (!in_array($user->role, ['super_admin', 'capitania'], true)) {
            return response()->json(['ok' => false, 'message' => 'No autorizado'], 403);
        }

        $notification = Notification::find($id);

        if (!$notification) {
            return response()->json(['ok' => false, 'message' => 'Notificación no encontrada'], 404);
        }

        $notification->markAsRead();

        return response()->json(['ok' => true]);
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        $user = auth()->user();

        if (!in_array($user->role, ['super_admin', 'capitania'], true)) {
            return response()->json(['ok' => false, 'message' => 'No autorizado'], 403);
        }

        Notification::unread()->update(['read_at' => now()]);

        return response()->json(['ok' => true, 'marked_count' => Notification::unread()->count()]);
    }

    public function unreadCount(Request $request): JsonResponse
    {
        $user = auth()->user();

        if (!in_array($user->role, ['super_admin', 'capitania'], true)) {
            return response()->json(['unread_count' => 0]);
        }

        $count = Notification::unread()->count();

        return response()->json(['unread_count' => $count]);
    }
}
