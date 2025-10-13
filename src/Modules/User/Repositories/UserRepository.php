<?php

namespace App\Modules\User\Repositories;

use App\Core\Repositories\BaseRepository;
use App\Modules\User\Database\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class UserRepository extends BaseRepository
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?User
    {
        return $this->findBy('email', $email);
    }

    /**
     * Find active users
     */
    public function findActiveUsers(): Collection
    {
        return $this->query()->active()->get();
    }

    /**
     * Find verified users
     */
    public function findVerifiedUsers(): Collection
    {
        return $this->query()->verified()->get();
    }

    /**
     * Search users
     */
    public function searchUsers(string $search, int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->search($search)
            ->paginate($perPage);
    }

    /**
     * Get users with filters
     */
    public function getUsersWithFilters(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->query();

        // Apply filters
        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (isset($filters['email_verified'])) {
            if ($filters['email_verified']) {
                $query->whereNotNull('email_verified_at');
            } else {
                $query->whereNull('email_verified_at');
            }
        }

        if (isset($filters['search'])) {
            $query->search($filters['search']);
        }

        // Apply sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';
        $query->orderBy($sortBy, $sortDirection);

        return $query->paginate($perPage);
    }

    /**
     * Get user statistics
     */
    public function getUserStats(): array
    {
        return [
            'total' => $this->count(),
            'active' => $this->query()->active()->count(),
            'verified' => $this->query()->verified()->count(),
            'inactive' => $this->query()->where('is_active', false)->count(),
            'unverified' => $this->query()->whereNull('email_verified_at')->count(),
        ];
    }
}
