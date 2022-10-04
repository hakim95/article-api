<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RegistrationController extends AbstractController
{
    #[Route('/api/register', name: 'app_registration', methods: ['POST'])]
    public function index(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ): JsonResponse {
        $user = new User();
        $username = $request->request->get('username');
        $password = $request->request->get('password');

        $user->setUsername($username);
        if (!empty($password)) {
            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $password
            );
            $user->setPassword($hashedPassword);
        }

        $errors = $validator->validate($user);

        if (count($errors) > 0) {
            return $this->json(
                [
                    'code' => Response::HTTP_BAD_REQUEST,
                    'message' => (string) $errors
                ],
                Response::HTTP_BAD_REQUEST
            );
        }

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json(
            [
                'code' => Response::HTTP_OK,
                'message' => 'your account has been registered'
            ]
        );
    }
}
