<?php

namespace App\Models\Queries;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class UserQuery extends QueryBuilder
{
    public function __construct()
    {
        parent::__construct(User::query());
        $this->allowedIncludes('topics', 'replies.topic');
    }
}