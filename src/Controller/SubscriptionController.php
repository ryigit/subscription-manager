<?php

namespace App\Controller;

use App\Entity\Subscription;
use App\Entity\User;
use App\Entity\UserSubscription;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SubscriptionController extends AbstractController
{
    #[Route('/api/subscriptions', name: 'subscriptions.all', methods: ['GET'])]
    public function getSubscriptions(EntityManagerInterface $entityManager): JsonResponse
    {
        $subscriptions = $entityManager->getRepository(Subscription::class)->findAll();

        return new JsonResponse(['subscriptions' => $subscriptions]);
    }

    #[Route('/api/subscriptions/{id}', name: 'subscriptions.get', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getSubscription(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $subscription = $entityManager->getRepository(Subscription::class)->find($id);

        if(!$subscription) {
            throw $this->createNotFoundException('The Subscription Does Does Not Exist');
        }

        return new JsonResponse($subscription);
    }

    #[Route('/api/subscriptions/{id}/subscribe', name: 'subscriptions.subscribe', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function subscribeToItem(int $id, EntityManagerInterface $entityManager): Response
    {
        //ToDo: What if user already have an active subscription?
        $subscription = $entityManager->getRepository(Subscription::class)->find($id);

        if(!$subscription) {
            throw $this->createNotFoundException('The Subscription Does Does Not Exist');
        }

        /** @var User $user */
        $user = $this->getUser();

        $userSubscription = new UserSubscription();
        $userSubscription->setUser($user);
        $userSubscription->setSubscription($subscription);
        $userSubscription->setStatus('active');
        $userSubscription->setStartDate(date_create('today'));
        $userSubscription->setEndDate(date_create('+30 days'));

        $entityManager->persist($userSubscription);

        //$entityManager->persist($user);

        $entityManager->flush();

        return new Response('Subscribed Successfully', Response::HTTP_CREATED);
    }

    #[Route('/api/subscriptions/{id}/unsubscribe', name: 'subscriptions.unsubscribe', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function unSubscribeToItem(int $id, EntityManagerInterface $entityManager): Response
    {
        $userSubscription = $entityManager->getRepository(UserSubscription::class)->find($id);

        if(!$userSubscription) {
            throw $this->createNotFoundException('The User Subscription Does Does Not Exist');
        }

        /** @var User $user */
        $user = $this->getUser();
        $user->removeUserSubscription($userSubscription);

        $entityManager->persist($user);
        $entityManager->flush();

        return new Response('Unsubscribed', Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/subscriptions/me', name: 'subscriptions.me', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function userSubscriptions(EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        return new JsonResponse($user->getUserSubscriptions()->toArray());
    }
}