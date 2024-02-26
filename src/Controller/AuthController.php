<?php

namespace App\Controller;

use App\Entity\ApiToken;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;

use phpDocumentor\Reflection\PseudoTypes\StringValue;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AuthController extends AbstractController
{
    #[OA\Response(
        response: 200,
        description: 'Returns User Token',
    )]
    #[OA\Parameter(
        name: 'email',
        description: 'Email address',
        in: 'query',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Parameter(
        name: 'password',
        description: 'Password',
        in: 'query',
        schema: new OA\Schema(type: 'string')
    )]
    #[Route('/api/auth/login', name: 'auth.login', methods: ['POST'])]
    public function login(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
    ): Response
    {
        $data = json_decode($request->getContent(), true);

        $email = $data['email'];
        $password = $data['password'];

        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

        if (!$user || !$passwordHasher->isPasswordValid($user, $password)) {
            throw new BadCredentialsException('Invalid Email or Password');
        }


        $token = $user->getApiTokens()->first()->getToken();

        return new JsonResponse(['token' => $token]);
    }

    #[OA\Response(
        response: 200,
        description: 'Returns success message',
    )]
    #[OA\Parameter(
        name: 'username',
        description: 'Name of the user',
        in: 'query',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Parameter(
        name: 'email',
        description: 'Email address',
        in: 'query',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Parameter(
        name: 'password',
        description: 'Password',
        in: 'query',
        schema: new OA\Schema(type: 'string')
    )]
    #[Route('/api/auth/register', name: 'auth.register', methods: ['POST'])]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ): Response
    {
        $data = json_decode($request->getContent(), true);

        $user = new User();
        $user->setName($data['name']);
        $user->setEmail($data['email']);

        $encodedPassword = $passwordHasher->hashPassword($user, $data['password']);
        $user->setPassword($encodedPassword);

        $errors = $validator->validate($user);

        if (count($errors) > 0) {
            $errorsString = (string) $errors;

            return new Response($errorsString, Response::HTTP_BAD_REQUEST);
        }

        $entityManager->persist($user);

        //Create a token for the user.
        $token = new ApiToken();
        $token->setToken(bin2hex(random_bytes(32)));
        $token->setOwnedBy($user);

        $entityManager->persist($token);

        $entityManager->flush();

        return new Response('User registered successfully', Response::HTTP_CREATED);

    }
}