<?php

namespace App\Core\Contracts;

interface HasStatus
{
    /**
     * Get the column name for the status
     */
    public function getStatusColumn(): string;


    /**
     * Get the allowed statuses for moderators
     */
    public function getModeratorAllowedStatuses(): array;

}
