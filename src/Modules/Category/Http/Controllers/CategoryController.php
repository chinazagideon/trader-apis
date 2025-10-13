<?php

namespace App\Modules\Category\Http\Controllers;

use App\Core\Controllers\CrudController;
use App\Modules\Category\Services\CategoryService;
use App\Core\Http\ServiceResponse;
use Illuminate\Http\JsonResponse;

class CategoryController extends CrudController
{
    public function __construct(
        private CategoryService $categoryService
    ) {
        parent::__construct($categoryService);
    }

    public function hello(): JsonResponse
    {
        return $this->successResponse([], 'Hello from Category module');
    }
}
