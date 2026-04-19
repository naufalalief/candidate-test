<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Controller for managing user notifications.
 *
 * Handles marking notifications as read (single and bulk) and deleting them.
 */
class NotificationController extends Controller
{
    /**
     * Mark all unread notifications as read.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function markAllRead(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->unreadNotifications->markAsRead();

        if ($request->expectsJson()) {
            return response()->json(['status' => 'ok']);
        }

        return back();
    }

    /**
     * Mark a single notification as read.
     *
     * @param  Request  $request
     * @param  string   $id  The notification UUID.
     * @return \Illuminate\Http\JsonResponse
     */
    public function markRead(Request $request, string $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $notification = $user->notifications()->where('id', $id)->first();

        if ($notification) {
            $notification->markAsRead();
        }

        return response()->json(['status' => 'ok']);
    }

    /**
     * Delete a single notification.
     *
     * @param  Request  $request
     * @param  string   $id  The notification UUID.
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request, string $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->notifications()->where('id', $id)->delete();

        if ($request->expectsJson()) {
            return response()->json(['status' => 'ok']);
        }

        return back();
    }
}
