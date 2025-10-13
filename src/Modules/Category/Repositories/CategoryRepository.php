<?php

namespace App\Modules\Category\Repositories;

use App\Core\Repositories\BaseRepository;
use App\Modules\Category\Database\Models\Category;

class CategoryRepository extends BaseRepository
{
    protected string $serviceName = 'CategoryRepository';
    public function __construct(Category $model)
    {
        parent::__construct($model);
    }
}
