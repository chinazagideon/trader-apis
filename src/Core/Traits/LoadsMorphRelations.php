<?php

namespace App\Core\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Core\Contracts\MorphRepositoryInterface;

trait LoadsMorphRelations
{
    /**
     * Get the name of the morph relationship (e.g., 'payable', 'transactable')
     * Override this method in your repository if the relationship name differs
     *
     * @return string
     */
    // protected function getMorphRelationshipName(): string
    // {
    //     // Try to detect from model traits or default to common names
    //     $traits = class_uses_recursive($this->model);

    //     foreach ($traits as $trait) {
    //         $traitName = class_basename($trait);

    //         // Detect BelongsToMorph traits (e.g., BelongsToPayable -> 'payable')
    //         if (preg_match('/BelongsTo(\w+)/', $traitName, $matches)) {
    //             return strtolower($matches[1]);
    //         }
    //     }

    //     // Fallback: try common morph relationship names
    //     $commonNames = ['payable', 'transactable', 'morphable', 'entity'];
    //     foreach ($commonNames as $name) {
    //         if (method_exists($this->model, $name)) {
    //             return $name;
    //         }
    //     }

    //     throw new \RuntimeException(
    //         'Could not determine morph relationship name for ' . get_class($this->model) .
    //         '. Override getMorphRelationshipName() in your repository.'
    //     );
    // }

    /**
     * Load morph relations on a query builder
     * Only works if repository implements MorphRepositoryInterface
     *
     * @param Builder $query
     * @return Builder
     */
    protected function withMorphRelations(Builder $query): Builder
    {
        if (!$this instanceof MorphRepositoryInterface) {
            return $query;
        }

        $morphRelationName = $this->getMorphRelationshipName();
        $morphRelations = $this->morphToRelations();

        if (empty($morphRelations)) {
            return $query;
        }

        $query->with([
            $morphRelationName => function ($morphTo) use ($morphRelations) {
                $morphTo->morphWith($morphRelations);
            }
        ]);

        return $query;
    }

    /**
     * Load morph relations on a model instance
     *
     * @param Model $model
     * @return Model
     */
    protected function loadMorphRelations(Model $model): Model
    {
        if (!$this instanceof MorphRepositoryInterface) {
            return $model;
        }

        $morphRelationName = $this->getMorphRelationshipName();
        $morphRelations = $this->morphToRelations();

        if (empty($morphRelations)) {
            return $model;
        }

        $model->load([
            $morphRelationName => function ($morphTo) use ($morphRelations) {
                $morphTo->morphWith($morphRelations);
            }
        ]);

        return $model;
    }

    /**
     * Create a query with morph relations and regular relationships
     * Combines morph relations with default relationships
     *
     * @param Builder $query
     * @param array $additionalRelationships
     * @return Builder
     */
    protected function withAllRelations(Builder $query, array $additionalRelationships = []): Builder
    {
        // Load morph relations first
        $query = $this->withMorphRelations($query);

        // Then load regular relationships
        if ($this->usesLoadsRelationshipsTrait()) {
            $relationships = !empty($additionalRelationships)
                ? $additionalRelationships
                : $this->getDefaultRelationships();

            $query = $this->withRelationships($query, $relationships);
        }

        return $query;
    }

    /**
     * Check if this repository uses the LoadsRelationships trait
     */
    protected function usesLoadsRelationshipsTrait(): bool
    {
        return in_array(LoadsRelationships::class, class_uses_recursive($this));
    }
}
