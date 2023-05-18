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
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use Psr\Cache\CacheItemPoolInterface;

class UserController extends AbstractController
{
    use RequestValidationTrait;
    /**
     * This API endpoint retrieves a list of users from the system. It allows you to fetch multiple user records at once, providing useful information about each user.
     *
     * @Route("/api/v1/users", methods={"GET"})
     * @OA\Response(
     *     response=200,
     *     description="List of users",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=User::class, groups={"full"}))
     *     )
     * )
     * @OA\Parameter(
     *     name="is_active",
     *     in="query",
     *     description="Is the user is active or not",
     *     @OA\Schema(type="boolean")
     * )
     * @OA\Parameter(
     *     name="is_member",
     *     in="query",
     *     description="Is the user is member or not",
     *     @OA\Schema(type="boolean")
     * )
     * @OA\Parameter(
     *     name="last_login_at",
     *     in="query",
     *     description="Last time the user logged in to the application",
     *     @OA\Schema(type="string"),
     *     example="2020-12-12 to 2022-12-12"
     * )
     * @OA\Parameter(
     *     name="user_type",
     *     in="query",
     *     description="User type, could be 1, 2 or 3",
     *     @OA\Schema(type="string"),
     *     example="1"
     * )
     * @OA\Parameter(
     *     name="pagination",
     *     in="query",
     *     description="The number of user to fetch per page",
     *     @OA\Schema(type="string")
     * )
     * @OA\Parameter(
     *     name="page",
     *     in="query",
     *     description="The number of page to fetch",
     *     @OA\Schema(type="string")
     * )
     * @OA\Tag(name="users")
     */
    public function index(Request $request, UserRepository $userRepository, UserResource $userResource, CacheItemPoolInterface $cache): JsonResponse
    {
        $userList = [];
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
            $cacheKey = $userRepository->getCacheKey($filters, $page, $pagination);
            $cacheItem = $cache->getItem($cacheKey);

            if ($cacheItem->isHit()) {
                $cachedData = $cacheItem->get();
                $userList = $cachedData['users'];
                $totalUsers = $cachedData['totalUsers'];
                $pagination = $cachedData['pagination'];
                $page = $cachedData['page'];
            } else {
                $users = $userRepository->findByFilters($filters);
                $userList = array_map(fn(User $user) => $userResource->toArray($user), $users);
                $totalUsers = $userRepository->countByFilters($filters);

                $cacheItem = $cache->getItem($cacheKey);
                $cacheItem->set([
                    'users' => $userList,
                    'totalUsers' => $totalUsers,
                    'pagination' => $pagination,
                    'page' => $page,
                ]);
                $cacheItem->expiresAfter($userRepository::ONE_HOUR_CACHE);
                $cache->save($cacheItem);
            }

            return $this->json(Response::toArray($userList, $totalUsers, $pagination, $page));
        } catch (\Exception $e) {
            //TODO: Log error and return a generic response
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }
}
