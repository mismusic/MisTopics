<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\ResourceRequest;
use App\Models\Resource;
use App\Services\TopicService;
use Illuminate\Http\Request;

class ResourcesController extends Controller
{

    public function store(ResourceRequest $request, Resource $resource, TopicService $topicService)
    {
        $response = $topicService->uploadFile($request, $resource);
        return $response;
    }

}
