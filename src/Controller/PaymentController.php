<?php

namespace App\Controller;

use App\Entity\Subscription;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class PaymentController extends AbstractController
{
    #[Security(name: 'Bearer')]
    #[Route('/api/payment', name: 'payment.make', methods: ['POST'])]
    public function makePayment(int $subscription_id, EntityManagerInterface $entityManager): JsonResponse
    {
        $subscription = $entityManager->getRepository(Subscription::class)->find($subscription_id);
        $user = $this->getUser();
        // Assume that there is a payment logic here.
        //$this->makePayment(int $userId, int $subscription->getPrice());

        return new JsonResponse(['status' => 'success']);
    }
}