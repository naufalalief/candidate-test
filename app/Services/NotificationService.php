<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\DataChangedNotification;
use Illuminate\Support\Facades\Auth;

/**
 * Service class for dispatching data-change notifications.
 *
 * Centralises notification logic so controllers remain thin.
 */
class NotificationService
{
    /**
     * Notify the authenticated user about a data change.
     *
     * @param  string       $action  The action performed: 'created', 'updated', 'deleted', 'imported'.
     * @param  string       $type    The entity type label (e.g. 'Supplier', 'Layup', 'Layer').
     * @param  string       $name    The name or label of the affected entity.
     * @param  string|null  $url     Optional URL linking to the affected entity.
     * @return void
     */
    public function notifyUser(string $action, string $type, string $name, ?string $url = null): void
    {
        /** @var User|null $user */
        $user = Auth::user();

        if ($user) {
            $user->notify(new DataChangedNotification($action, $type, $name, $url));
        }
    }
}
