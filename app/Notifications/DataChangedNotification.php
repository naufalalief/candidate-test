<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Database notification sent when supplier, layup, or layer data changes.
 *
 * Stores action type, entity type, entity name, and an optional URL
 * in the notifications table for display in the UI bell dropdown.
 */
class DataChangedNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @param  string       $action  The action performed: 'created', 'updated', 'deleted', 'imported'.
     * @param  string       $type    The entity type label (e.g. 'Supplier').
     * @param  string       $name    The name or label of the affected entity.
     * @param  string|null  $url     Optional URL linking to the affected entity.
     */
    public function __construct(
        public string $action,
        public string $type,
        public string $name,
        public ?string $url = null,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @param  object  $notifiable
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification for the database.
     *
     * @param  object  $notifiable
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'action'  => $this->action,
            'type'    => $this->type,
            'name'    => $this->name,
            'url'     => $this->url,
            'message' => $this->buildMessage(),
        ];
    }

    /**
     * Build a human-readable message describing the data change.
     *
     * @return string
     */
    private function buildMessage(): string
    {
        return match ($this->action) {
            'created'  => "{$this->type} \"{$this->name}\" was created.",
            'updated'  => "{$this->type} \"{$this->name}\" was updated.",
            'deleted'  => "{$this->type} \"{$this->name}\" was deleted.",
            'imported' => "Data was imported successfully.",
            default    => "{$this->type} \"{$this->name}\" was {$this->action}.",
        };
    }
}
