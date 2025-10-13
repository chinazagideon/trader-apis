<?php

namespace App\Modules\Auth\Policies;

use App\Modules\User\Database\Models\User;

class AuthPolicy
{
    /**
     * Determine if the user can view any auth resources
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('auth.view');
    }

    /**
     * Determine if the user can view auth resources
     */
    public function view(User $user, $model): bool
    {
        return $user->hasPermission('auth.view') || $user->id === $model->id;
    }

    /**
     * Determine if the user can create auth resources
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('auth.create');
    }

    /**
     * Determine if the user can update auth resources
     */
    public function update(User $user, $model): bool
    {
        return $user->hasPermission('auth.update') || $user->id === $model->id;
    }

    /**
     * Determine if the user can delete auth resources
     */
    public function delete(User $user, $model): bool
    {
        return $user->hasPermission('auth.delete') || $user->id === $model->id;
    }

    /**
     * Determine if the user can manage tokens
     */
    public function manageTokens(User $user): bool
    {
        return $user->hasPermission('auth.manage_tokens');
    }

    /**
     * Determine if the user can reset passwords
     */
    public function resetPasswords(User $user): bool
    {
        return $user->hasPermission('auth.reset_passwords');
    }

    /**
     * Determine if the user can verify emails
     */
    public function verifyEmails(User $user): bool
    {
        return $user->hasPermission('auth.verify_emails');
    }
}
