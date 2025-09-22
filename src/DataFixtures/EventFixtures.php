<?php

namespace App\DataFixtures;

use App\Entity\Event;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class EventFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $events = ['black_friday', 'black_hour'];

        foreach ($events as $name) {
            // Vérifier si l'événement existe déjà
            $existingEvent = $manager->getRepository(Event::class)->findOneBy(['eventName' => $name]);
            if ($existingEvent) {
                continue; // passer si déjà présent
            }

            $event = new Event();
            $event->setEventName($name);
            $event->setIsActive(false); // OFF par défaut
            $manager->persist($event);
        }

        $manager->flush();
    }
}
