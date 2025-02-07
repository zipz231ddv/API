<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/v1')]
class TestController extends AbstractController
{
    private array $users = [
        ['id' => '1', 'email' => 'zipz231_ddv@student.ztu.edu.ua', 'name' => 'ddv1'],
        ['id' => '2', 'email' => 'zipz231_ddv@student.ztu.edu.ua', 'name' => 'ddv2'],
        ['id' => '3', 'email' => 'zipz231_ddv@student.ztu.edu.ua', 'name' => 'ddv3'],
    ];

    #[Route('/users', name: 'app_collection_users', methods: ['GET'])]
    #[IsGranted("ROLE_ADMIN")]
    public function getCollection(): JsonResponse
    {
        return new JsonResponse(['data' => $this->users], Response::HTTP_OK);
    }

    #[Route('/users/{id}', name: 'app_item_users', methods: ['GET'])]
    public function getItem(string $id): JsonResponse
    {
        $userData = $this->findUserById($id);
        return new JsonResponse(['data' => $userData], Response::HTTP_OK);
    }

    #[Route('/users', name: 'app_create_users', methods: ['POST'])]
    public function createItem(Request $request): JsonResponse
    {
        $requestData = json_decode($request->getContent(), true);

        if (empty($requestData['email']) || empty($requestData['name'])) {
            throw new UnprocessableEntityHttpException("name and email are required");
        }

        if (!filter_var($requestData['email'], FILTER_VALIDATE_EMAIL)) {
            throw new UnprocessableEntityHttpException("Invalid email format");
        }

        $newId = count($this->users) + 1;
        $newUser = ['id' => $newId, 'name' => $requestData['name'], 'email' => $requestData['email']];
        $this->users[] = $newUser;

        return new JsonResponse(['data' => $newUser], Response::HTTP_CREATED);
    }

    #[Route('/users/{id}', name: 'app_delete_users', methods: ['DELETE'])]
    public function deleteItem(string $id): JsonResponse
    {
        foreach ($this->users as $key => $user) {
            if ($user['id'] === $id) {
                unset($this->users[$key]);
                return new JsonResponse([], Response::HTTP_NO_CONTENT);
            }
        }

        throw new NotFoundHttpException("User with id $id not found");
    }

    #[Route('/users/{id}', name: 'app_update_users', methods: ['PATCH'])]
    public function updateItem(string $id, Request $request): JsonResponse
    {
        $requestData = json_decode($request->getContent(), true);

        if (empty($requestData['name'])) {
            throw new UnprocessableEntityHttpException("name is required");
        }

        foreach ($this->users as &$user) {
            if ($user['id'] === $id) {
                $user['name'] = $requestData['name'];
                return new JsonResponse(['data' => $user], Response::HTTP_OK);
            }
        }

        throw new NotFoundHttpException("User with id $id not found");
    }

    public function findUserById(string $id): array
    {
        foreach ($this->users as $user) {
            if ($user['id'] == $id) {
                return $user;
            }
        }

        throw new NotFoundHttpException("User with id $id not found");
    }
}