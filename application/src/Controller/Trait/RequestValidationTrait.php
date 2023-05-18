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

        if ($request->query->get('is_active') !== null) {
            $isActive = filter_var($request->query->get('is_active'), FILTER_VALIDATE_BOOLEAN);
            $filters = array_merge($filters, ['is_active' => $isActive]);
        }

        if ($request->query->get('is_member') !== null) {
            $isMember = filter_var($request->query->get('is_member'), FILTER_VALIDATE_BOOLEAN);
            $filters = array_merge($filters, ['is_member' => $isMember]);
        }

        if ($request->query->get('pagination') !== null) {
            $pagination = filter_var($request->query->get('pagination'), FILTER_VALIDATE_INT);
            $filters = array_merge($filters, ['pagination' => $pagination]);
        }

        if ($request->query->get('page') !== null) {
            $page = filter_var($request->query->get('user_type'), FILTER_VALIDATE_INT);
            $filters = array_merge($filters, ['user_type' => $page]);
        }

        $constraints = new Assert\Collection([
            'is_active' => new Assert\Optional(new Assert\Type(['type' => 'bool'])),
            'is_member' => new Assert\Optional(new Assert\Type(['type' => 'bool'])),
            'last_login_at' => new Assert\Optional(new Assert\Type(['type' => 'string'])),
            'user_type' => new Assert\Optional(new Assert\Choice(['choices' => ['1', '2', '3']])),
            'pagination' => new Assert\Optional([new Assert\Type(['type' => 'int']),]),
            'page' => new Assert\Optional([
                new Assert\Type(['type' => 'int']),
                new Assert\Callback(function ($value, $context) {
                    if ($value <= 0) {
                        $context->buildViolation('Page must be an integer greater than 0.')
                            ->atPath('page')
                            ->addViolation();
                    }
                }),
            ]),
        ]);

        $violations = $validator->validate($filters, $constraints);

        if (count($violations) > 0) {
            throw new BadRequestHttpException((string)$violations);
        }
    }
}