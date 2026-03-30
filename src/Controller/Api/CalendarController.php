<?php

namespace App\Controller\Api;

use App\Repository\MeetingRepository;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/calendar')]
#[OA\Tag(name: 'Calendar')]
class CalendarController extends AbstractController
{
    public function __construct(
        private MeetingRepository $meetingRepository,
    ) {
    }

    #[Route('/events', methods: ['GET'])]
    #[OA\Get(summary: 'Get calendar events in FullCalendar format')]
    #[OA\Parameter(name: 'start', in: 'query', required: true, schema: new OA\Schema(type: 'string', format: 'date'))]
    #[OA\Parameter(name: 'end', in: 'query', required: true, schema: new OA\Schema(type: 'string', format: 'date'))]
    #[OA\Response(response: 200, description: 'List of calendar events')]
    #[OA\Response(response: 400, description: 'Missing start/end parameters')]
    public function events(Request $request): JsonResponse
    {
        $start = $request->query->get('start');
        $end = $request->query->get('end');

        if (!$start || !$end) {
            return new JsonResponse(['error' => 'start and end query parameters are required.'], 400);
        }

        $startDate = new \DateTimeImmutable($start);
        $endDate = new \DateTimeImmutable($end);

        $meetings = $this->meetingRepository->findByDateRange($startDate, $endDate);

        $events = [];
        foreach ($meetings as $meeting) {
            $color = match ($meeting->getStatus()) {
                'scheduled' => '#3788d8',
                'completed' => '#28a745',
                'cancelled' => '#dc3545',
                default => '#6c757d',
            };

            $clientName = null;
            if ($meeting->getClient()) {
                $clientName = $meeting->getClient()->getCompanyName();
            }

            $events[] = [
                'id' => $meeting->getId(),
                'title' => $meeting->getTitle(),
                'start' => $meeting->getStartAt()?->format('c'),
                'end' => $meeting->getEndAt()?->format('c'),
                'color' => $color,
                'extendedProps' => [
                    'type' => 'meeting',
                    'status' => $meeting->getStatus(),
                    'clientName' => $clientName,
                ],
            ];
        }

        return new JsonResponse($events);
    }

    #[Route('/upcoming', methods: ['GET'])]
    #[OA\Get(summary: 'Get upcoming meetings')]
    #[OA\Parameter(name: 'limit', in: 'query', schema: new OA\Schema(type: 'integer', default: 10))]
    #[OA\Response(response: 200, description: 'List of upcoming meetings')]
    public function upcoming(Request $request): JsonResponse
    {
        $limit = (int) $request->query->get('limit', 10);

        $meetings = $this->meetingRepository->findUpcoming($limit);

        $data = [];
        foreach ($meetings as $meeting) {
            $data[] = [
                'id' => $meeting->getId(),
                'title' => $meeting->getTitle(),
                'description' => $meeting->getDescription(),
                'startAt' => $meeting->getStartAt()?->format('c'),
                'endAt' => $meeting->getEndAt()?->format('c'),
                'status' => $meeting->getStatus(),
                'location' => $meeting->getLocation(),
                'client' => $meeting->getClient() ? [
                    'id' => $meeting->getClient()->getId(),
                    'company_name' => $meeting->getClient()->getCompanyName(),
                    'contact_person' => $meeting->getClient()->getContactPerson(),
                ] : null,
            ];
        }

        return new JsonResponse($data);
    }
}
