<?php

declare(strict_types=1);

namespace App\Controller\Api\Resource;

use App\Entity\User;

class UserResource
{
    public function toArray(User $user): array
    {
        return [
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
            'is_member' => $user->isIsMember(),
            'is_active' => $user->isIsActive(),
            'user_type' => $user->getUserType(),
            'last_login_at' => $user->getLastLoginAt()?->format('Y-m-d H:i:s'),
        ];
    }
}