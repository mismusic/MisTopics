<?php

namespace App\Http\Resources;

use App\Common\ApiReturnCode;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'username' => $this->username,
            'phone' => $this->phone,
            'email' => $this->email,
            'avatar' => $this->avatar,
            'weixin_openid' => $this->weixin_openid,
            'weixin_unionid' => $this->weixin_unionid,
            'introduction' => $this->introduction,
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
