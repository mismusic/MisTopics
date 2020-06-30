<?php

namespace App\Http\Resources;

use App\Common\ApiReturnCode;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'pid' => $this->pid,
            'name' => $this->name,
            'description' => $this->description,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    public function with($request)
    {
        return [
            'code' => ApiReturnCode::API_RETURN_CODE_SUCCESS,
            'msg' => ApiReturnCode::getReturnMessage(ApiReturnCode::API_RETURN_CODE_SUCCESS),
        ];
    }
}
