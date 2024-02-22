<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\Routing\Annotation\Route;

class AuthController extends AbstractController
{
    #[Route('/api/auth/login', name: 'auth.login')]
    public function login()
    {

    }

    #[Route('/api/auth/register', name: 'auth.register')]
    public function register()
    {

    }
}