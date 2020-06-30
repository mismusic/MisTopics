<?php

namespace App\Models\Queries;

use App\Models\Topic;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class TopicQuery extends QueryBuilder
{
    public function __construct()
    {
        parent::__construct(Topic::query());
        $this->allowedIncludes('user', 'category')
            ->allowedFilters([
                'title',  // like搜索
                AllowedFilter::exact('user_id'),
                AllowedFilter::exact('category_id'), // 精确搜索
                AllowedFilter::scope('order')->default('create'),  // 作用域搜索
            ]);
    }
}