<?php

namespace App\Controller;

use App\Entity\Event;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\EventRepository;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class EventController extends AbstractController
{
    #[Route('/admin/api/event/toggle', name: 'app_event_toggle', methods: ['POST'])]
    public function toggleEvent(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON data');
            }
            
            $eventName = $data['eventName'] ?? null;
            $isActive = filter_var($data['isActive'] ?? false, FILTER_VALIDATE_BOOLEAN);

            if (!$eventName) {
                return $this->json(['success' => false, 'message' => 'Event name is required'], 400);
            }

            $event = $entityManager->getRepository(Event::class)->findOneBy(['eventName' => $eventName]);

            if (!$event) {
                $event = new Event();
                $event->setEventName($eventName);
                $event->setIsActive($isActive);
                $entityManager->persist($event);
            } else {
                $event->setIsActive($isActive);
            }

            $entityManager->flush();

            return $this->json([
                'success' => true,
                'isActive' => $event->getIsActive()
            ]);
            
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    #[Route('/api/admin/events/status', name: 'app_events_status', methods: ['GET'])]
    #[IsGranted('PUBLIC_ACCESS')]
    public function getEventsStatus(EventRepository $eventRepository): JsonResponse
    {
        $events = $eventRepository->findAll();
        $status = [];
        
        foreach ($events as $event) {
            $status[$event->getEventName()] = $event->getIsActive();
        }
        
        // Ensure both events exist in the response
        if (!array_key_exists('black_friday', $status)) {
            $status['black_friday'] = false;
        }
        if (!array_key_exists('black_hour', $status)) {
            $status['black_hour'] = false;
        }
        
        return $this->json([
            'success' => true,
            'events' => $status
        ]);
    }
}
