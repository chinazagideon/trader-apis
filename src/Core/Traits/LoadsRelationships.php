<?php

namespace App\Core\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

trait LoadsRelationships
{
    /**
     * Load relationships on a model instance
     */
    public function loadRelationships(Model $model, array $relationships = []): Model
    {
        $relationshipsToLoad = empty($relationships) ? $this->getDefaultRelationships() : $relationships;

        if (!empty($relationshipsToLoad)) {
            $model->load($relationshipsToLoad);
        }

        return $model;
    }

    /**
     * Create a query builder with relationships pre-loaded
     * Supports nested relationships using dot notation
     */
    public function withRelationships(Builder $query, array $relationships = []): Builder
    {
        $relationshipsToLoad = empty($relationships)
            ? $this->getDefaultRelationships()
            : $relationships;

        if (!empty($relationshipsToLoad)) {
            $query->with($relationshipsToLoad);
        }

        return $query;
    }

    /**
     * Ensure relationships are loaded on a model
     */
    public function ensureRelationshipsLoaded(Model $model, array $relationships = []): Model
    {
        $relationshipsToLoad = empty($relationships) ? $this->getDefaultRelationships() : $relationships;

        foreach ($relationshipsToLoad as $relationship) {
            if (!$model->relationLoaded($relationship)) {
                $model->load($relationship);
            }
        }

        return $model;
    }

    /**
     * Load relationships conditionally based on request parameters
     */
    public function loadRelationshipsFromRequest(Model $model, array $requestData = []): Model
    {
        $withParam = $requestData['with'] ?? '';

        if (empty($withParam)) {
            return $this->loadRelationships($model);
        }

        $requestedRelationships = array_map('trim', explode(',', $withParam));
        $validRelationships = $this->filterValidRelationships($requestedRelationships);

        if (!empty($validRelationships)) {
            $model->load($validRelationships);
        }

        return $model;
    }

    /**
     * Get default relationships for this model
     * Automatically detects relationships from traits used by the model
     */
    protected function getDefaultRelationships(): array
    {
        $relationships = [];

        // Get all traits used by the model
        $traits = class_uses_recursive($this->model);

        foreach ($traits as $trait) {
            $traitName = class_basename($trait);

            // Detect BelongsTo traits
            if (str_starts_with($traitName, 'BelongsTo')) {
                $relationshipName = strtolower(str_replace('BelongsTo', '', $traitName));
                $relationships[] = $relationshipName;
            }

            // Detect HasMany traits
            if (str_starts_with($traitName, 'HasMany')) {
                $relationshipName = strtolower(str_replace('HasMany', '', $traitName));
                $relationships[] = $relationshipName;
            }

            // Detect HasOne traits
            if (str_starts_with($traitName, 'HasOne')) {
                $relationshipName = strtolower(str_replace('HasOne', '', $traitName));
                $relationships[] = $relationshipName;
            }

            // Detect BelongsToMany traits
            if (str_starts_with($traitName, 'BelongsToMany')) {
                $relationshipName = strtolower(str_replace('BelongsToMany', '', $traitName));
                $relationships[] = $relationshipName;
            }
        }

        return array_unique($relationships);
    }

    /**
     * Filter relationships to only include valid ones
     */
    protected function filterValidRelationships(array $relationships): array
    {
        $validRelationships = [];

        foreach ($relationships as $relationship) {
            if ($this->isValidRelationship($relationship)) {
                $validRelationships[] = $relationship;
            }
        }

        return $validRelationships;
    }

    /**
     * Check if a relationship is valid for the model
     */
    protected function isValidRelationship(string $relationship): bool
    {
        // This can be overridden in specific repositories to validate relationships
        return method_exists($this->model ?? new class {}, $relationship);
    }

    /**
     * Create model with relationships loaded
     */
    public function createWithRelationships(array $data, array $relationships = []): Model
    {
        $model = $this->model->create($data);
        return $this->loadRelationships($model, $relationships);
    }

    /**
     * Find model with relationships loaded
     */
    public function findWithRelationships(int $id, array $relationships = []): ?Model
    {
        $model = $this->model->find($id);

        if ($model) {
            $model = $this->loadRelationships($model, $relationships);
        }

        return $model;
    }

    /**
     * Get all models with relationships loaded
     */
    public function allWithRelationships(array $relationships = []): \Illuminate\Database\Eloquent\Collection
    {
        $query = $this->model->newQuery();
        $query = $this->withRelationships($query, $relationships);

        return $query->get();
    }

    /**
     * Get paginated models with relationships loaded
     */
    public function paginateWithRelationships(int $perPage = 15, array $relationships = []): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = $this->model->newQuery();
        $query = $this->withRelationships($query, $relationships);

        return $query->paginate($perPage);
    }

}
