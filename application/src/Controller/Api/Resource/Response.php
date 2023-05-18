<?php

declare(strict_types=1);

namespace App\Controller\Api\Resource;

class Response
{
    public static function toArray(array $data, int $total, int $pagination, int $page): array
    {
        return [
            'data' => $data,
            'meta' => [
                'total' => $total,
                'per_page' => $pagination,
                'current_page' => $page,
                'last_page' => ceil($total / $pagination),
            ],
        ];
    }
}