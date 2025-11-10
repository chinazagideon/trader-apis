<?php

namespace App\Core\Contracts;

interface MorphRepositoryInterface
{
    public function morphToRelations(): array;

    public function getMorphRelationshipName(): string;
}
