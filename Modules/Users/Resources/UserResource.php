<?php

namespace Modules\Users\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // For nested user data (in posts/comments), only show id and name
        // For direct user endpoints, show full details
        $isNested = $request->route() &&
            (str_contains($request->route()->uri(), 'posts') ||
                str_contains($request->route()->uri(), 'comments'));

        if ($isNested) {
            return [
                'id' => $this->id,
                'name' => $this->name,
            ];
        }

        // Full user data for auth/user endpoints
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'created_at' => $this->created_at,
        ];
    }
}
