<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\MissingValue;

class ReplyResource extends JsonResource
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
        $data = [
            'id' => $this->id,
            'pid' => $this->pid,
            'content' => $this->content,
            'user' => $user,
            'topic' => new TopicResource($this->whenLoaded('topic')),
            'created_at' => $this->created_at->from(),
            'updated_at' => $this->updated_at->from(),
        ];
        if ($this->level) {
            $data['level'] = $this->level;
        }
        if ($this->children) {
            $data['children'] = $this->children;
        }
        return $data;
    }
}
