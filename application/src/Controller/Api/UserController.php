<?php

namespace App\Controller\Api;

use App\Controller\Api\Resource\Response;
use App\Controller\Api\Resource\UserResource;
use App\Controller\Trait\RequestValidationTrait;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    use RequestValidationTrait;

    #[Route('/users', name: 'users', methods: 'GET')]
    public function index(Request $request, UserRepository $userRepository, UserResource $userResource): JsonResponse
    {
        $this->validateRequest($request);
        $pagination = $request->query->getInt('pagination', 10);
        $page = $request->query->getInt('page', 1);
        $filters = [
            'isActive' => $request->query->get('is_active'),
            'isMember' => $request->query->get('is_member'),
            'lastLoginAt' => $request->query->get('last_login_at'),
            'userType' => $request->query->get('user_type'),
        ];

        try {
            $users = $userRepository->findByFilters($filters);
            $mappedUsers = array_map(fn(User $user) => $userResource->toArray($user), $users);
            $totalUsers = $userRepository->countByFilters($filters);

            return $this->json(Response::toArray($mappedUsers, $totalUsers, $pagination, $page));
        } catch (\Exception $e) {
            //TODO: Log error and return a generic response
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }
}
