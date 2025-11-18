<?php

namespace App\DTOs;

final class CreatePostData
{
    public function __construct(
        public readonly string $title,
        public readonly string $content,
        public readonly ?string $imagePath,
        public readonly int $userId
    ) {}

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'content' => $this->content,
            'image' => $this->imagePath,
            'user_id' => $this->userId,
        ];
    }
}
