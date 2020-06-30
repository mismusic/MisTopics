<?php

namespace App\Http\Resources;

use App\Common\ApiReturnCode;
use Illuminate\Http\Resources\Json\ResourceCollection;

class NotificationCollection extends ResourceCollection
{

    public $collects = NotificationResource::class;

    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'code' => ApiReturnCode::API_RETURN_CODE_SUCCESS,
            'msg' => ApiReturnCode::getReturnMessage(ApiReturnCode::API_RETURN_CODE_SUCCESS),
            'data' => $this->collection,
        ];
    }
}
