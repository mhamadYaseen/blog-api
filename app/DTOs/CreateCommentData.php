<?php

namespace App\DTOs;

final class CreateCommentData
{
    public function __construct(
        public readonly string $comment,
        public readonly int $userId,
        public readonly int $postId,
    ) {}

    public function toArray(): array
    {
        return [
            'comment' => $this->comment,
            'user_id' => $this->userId,
            'post_id' => $this->postId,
        ];
    }
}
