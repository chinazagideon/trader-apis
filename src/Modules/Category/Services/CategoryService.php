<?php

namespace App\Modules\Category\Services;

use App\Core\Services\BaseService;
use App\Core\Http\ServiceResponse;
use App\Modules\Category\Repositories\CategoryRepository;

class CategoryService extends BaseService
{
    protected string $serviceName = 'CategoryService';

    public function __construct(
        private CategoryRepository $CategoryRepository
    ){
        parent::__construct($CategoryRepository);
    }

}
