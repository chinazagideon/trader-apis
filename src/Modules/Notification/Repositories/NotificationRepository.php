<?php

namespace App\Modules\Notification\Repositories;

use App\Core\Repositories\BaseRepository;
use App\Modules\Notification\Database\Models\Notification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class NotificationRepository extends BaseRepository
{
    public function __construct(Notification $model)
    {
        parent::__construct($model);
    }

    /**
     * Get notifications for a specific entity
     *
     * @param string $type
     * @param int|string $id
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getForEntity(string $type, int|string $id, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model
            ->forEntity($type, $id)
            ->orderBy('created_at', 'desc');

        return $query->paginate($perPage);
    }

    /**
     * Get unread notifications for a specific entity
     *
     * @param string $type
     * @param int|string $id
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getUnreadForEntity(string $type, int|string $id, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->forEntity($type, $id)
            ->unread()
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get unread count for entity
     *
     * @param string $type
     * @param int|string $id
     * @return int
     */
    public function getUnreadCountForEntity(string $type, int|string $id): int
    {
        return $this->model
            ->forEntity($type, $id)
            ->unread()
            ->count();
    }

    /**
     * Mark notification as read
     *
     * @param string $id
     * @return bool
     */
    public function markAsRead(string $id): bool
    {
        $notification = $this->model->find($id);

        if ($notification) {
            $notification->markAsRead();
            return true;
        }

        return false;
    }

    /**
     * Mark all notifications as read for entity
     *
     * @param string $type
     * @param int|string $id
     * @return int
     */
    public function markAllAsReadForEntity(string $type, int|string $id): int
    {
        return $this->model
            ->forEntity($type, $id)
            ->unread()
            ->update(['read_at' => now()]);
    }

    /**
     * Get notifications by channel
     *
     * @param string $channel
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getByChannel(string $channel, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->whereJsonContains('channels_sent', $channel)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get failed notifications
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getFailed(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->whereNotNull('failed_channels')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Delete old read notifications
     *
     * @param int $days
     * @return int
     */
    public function deleteOldRead(int $days = 30): int
    {
        return $this->model
            ->read()
            ->where('read_at', '<', now()->subDays($days))
            ->delete();
    }
}

