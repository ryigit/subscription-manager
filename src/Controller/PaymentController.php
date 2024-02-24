<?php

namespace App\Controller;

use App\Entity\Subscription;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PaymentController
{
    #[Route('/api/payment', name: 'payment.make', methods: ['POST'])]
    public function makePayment(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $subscriptions = $entityManager->getRepository(Subscription::class)->find($request['subscription_id']);

        return new JsonResponse(['subscriptions' => $subscriptions]);
    }
}