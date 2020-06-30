<?php

namespace App\Http\Resources;

use App\Common\ApiReturnCode;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\MissingValue;

class TopicResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        if (! $this->whenLoaded('user') instanceof MissingValue) {
            $user = $this->whenLoaded('user')->only(['id', 'username', 'avatar']);
        } else {
            $user = $this->whenLoaded('user');
        }
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'description' => $this->description,
            'read_count' => $this->read_count,
            'reply_count' => $this->reply_count,
            'user' => $user,
            'category' => new CategoryResource($this->whenLoaded('category')),
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
