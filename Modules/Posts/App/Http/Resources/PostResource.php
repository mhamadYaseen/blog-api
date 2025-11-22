<?php

namespace Modules\Posts\App\Http\Resources;

use Modules\Users\App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'image' => $this->cover_image_url,
            'image_thumb' => $this->cover_image_thumb_url,
            'user' => new UserResource($this->whenLoaded('user')),
            'comments_count' => $this->when(isset($this->comments_count), $this->comments_count),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
