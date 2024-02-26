<?php

namespace App\Controller;

use App\Entity\Subscription;
use App\Entity\User;
use App\Entity\UserSubscription;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

class SubscriptionController extends AbstractController
{
    #[OA\Response(
        response: 200,
        description: 'Returns a list of all subscriptions',
    )]
    #[Route('/api/subscriptions', name: 'subscriptions.all', methods: ['GET'])]
    public function getSubscriptions(EntityManagerInterface $entityManager): JsonResponse
    {
        $subscriptions = $entityManager->getRepository(Subscription::class)->findAll();

        return new JsonResponse(['subscriptions' => $subscriptions]);
    }

    #[OA\Response(
        response: 200,
        description: 'Returns subscription with the given ID.',
    )]
    #[Security(name: 'Bearer')]
    #[Route('/api/subscriptions/{id}', name: 'subscriptions.get', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getSubscription(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $subscription = $entityManager->getRepository(Subscription::class)->find($id);

        if(!$subscription) {
            throw $this->createNotFoundException('The Subscription Does Does Not Exist');
        }

        return new JsonResponse($subscription);
    }

    #[OA\Response(
        response: 200,
        description: 'Subscribes to item with given Id for the active user',
    )]
    #[OA\Parameter(
        name: 'id',
        description: 'Subscription ID',
        in: 'query',
        schema: new OA\Schema(type: 'string')
    )]
    #[Security(name: 'Bearer')]
    #[Route('/api/subscriptions/{id}/subscribe', name: 'subscriptions.subscribe', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function subscribeToItem(int $id, EntityManagerInterface $entityManager, LoggerInterface $logger): Response
    {
        $subscription = $entityManager->getRepository(Subscription::class)->find($id);

        if(!$subscription) {
            throw $this->createNotFoundException('The Subscription Does Does Not Exist');
        }

        /** @var User $user */
        $user = $this->getUser();

        $logger->info('User ' . $user->getId() . ' will subscribe to Subscription ' . $subscription->getId());

        $existingUserSubscription = $entityManager->getRepository(UserSubscription::class)->findBy([
            'user' => $user,
            'subscription' => $subscription,
            'status' => 'active'
        ]);

        if($existingUserSubscription) {
            throw new BadRequestHttpException('User is already subscribed to this Subscription');
        }

        $userSubscription = new UserSubscription();
        $userSubscription->setUser($user);
        $userSubscription->setSubscription($subscription);
        $userSubscription->setStatus('active');
        $userSubscription->setStartDate(date_create('today'));
        $userSubscription->setEndDate(date_create('+30 days'));

        $entityManager->persist($userSubscription);

        $entityManager->flush();

        return new Response('Subscribed Successfully', Response::HTTP_CREATED);
    }

    #[OA\Response(
        response: 200,
        description: 'UnSubscribes from the item with given id for the active user',
    )]
    #[OA\Parameter(
        name: 'id',
        description: 'Subscription ID',
        in: 'query',
        schema: new OA\Schema(type: 'string')
    )]
    #[Security(name: 'Bearer')]
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
        //Might find a better way.
        $entityManager->remove($userSubscription);

        $entityManager->persist($user);
        $entityManager->flush();

        return new Response('Unsubscribed', Response::HTTP_NO_CONTENT);
    }

    #[OA\Response(
        response: 200,
        description: 'Returns active subscriptions of the active user',
    )]
    #[Security(name: 'Bearer')]
    #[Route('/api/subscriptions/me', name: 'subscriptions.me', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function userSubscriptions(EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        return new JsonResponse(['user_subscriptions' => $user->getUserSubscriptions()->toArray()]);
    }
}