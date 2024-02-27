<?php

namespace App\DataFixtures;

use App\Entity\Subscription;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Create a few subscriptions for testing.
        $subscription = new Subscription();
        $subscription->setName('Netflix');
        $subscription->setDescription('Monthly Netflix Subscription');
        $subscription->setPrice(20);
        $subscription->setDuration(30);
        $manager->persist($subscription);

        $subscription = new Subscription();
        $subscription->setName('Prime');
        $subscription->setDescription('Prime Weekly Subscription');
        $subscription->setPrice(5);
        $subscription->setDuration(7);
        $manager->persist($subscription);

        $subscription = new Subscription();
        $subscription->setName('Spotify');
        $subscription->setDescription('Yearly Spotify Subscription');
        $subscription->setPrice(80);
        $subscription->setDuration(365);
        $manager->persist($subscription);

        $manager->flush();
    }
}
