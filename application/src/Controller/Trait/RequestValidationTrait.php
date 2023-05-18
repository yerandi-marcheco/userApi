<?php

declare(strict_types=1);

namespace App\Controller\Trait;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints as Assert;

trait RequestValidationTrait
{
    public function validateRequest(Request $request): void
    {
        $validator = Validation::createValidator();
        $filters = $request->query->all();

        $isActive = filter_var($request->query->get('is_active'), FILTER_VALIDATE_BOOLEAN);
        $isMember = filter_var($request->query->get('is_member'), FILTER_VALIDATE_BOOLEAN);
        $filters = array_merge($filters, ['is_active' => $isActive, 'is_member' => $isMember]);

        $constraints = new Assert\Collection([
            'is_active' => new Assert\Optional(new Assert\Type(['type' => 'bool'])),
            'is_member' => new Assert\Optional(new Assert\Type(['type' => 'bool'])),
            'last_login_at' => new Assert\Optional(new Assert\Type(['type' => 'string'])),
            'user_type' => new Assert\Optional(new Assert\Choice(['choices' => ['1', '2', '3']]))
        ]);

        $violations = $validator->validate($filters, $constraints);

        if (count($violations) > 0) {
            throw new BadRequestHttpException((string)$violations);
        }
    }
}