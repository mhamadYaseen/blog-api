<?php

namespace App\DTOs;

final class UpdatePostData
{
    public function __construct(
        public readonly string $title,
        public readonly string $content,
    ) {}

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'content' => $this->content,
        ];
    }
}
