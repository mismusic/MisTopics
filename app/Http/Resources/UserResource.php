<?php

namespace App\Http\Resources;

use App\Common\ApiReturnCode;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\MissingValue;

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
            'email_verified_at' => $this->email_verified_at,
            'avatar' => $this->avatar,
            'weixin_openid' => $this->weixin_openid,
            'weixin_unionid' => $this->weixin_unionid,
            'introduction' => $this->introduction,
            'topics' => TopicResource::collection($this->whenLoaded('topics')),
            'replies' => ReplyResource::collection($this->whenLoaded('replies')),
            'created_at' => (string) $this->created_at,
            'updated_at' => (string) $this->updated_at,
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
