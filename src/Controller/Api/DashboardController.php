<?php

namespace App\Controller\Api;

use App\Repository\ClientRepository;
use App\Repository\DealRepository;
use App\Repository\MeetingRepository;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/dashboard')]
#[OA\Tag(name: 'Dashboard')]
class DashboardController extends AbstractController
{
    public function __construct(
        private ClientRepository $clientRepository,
        private DealRepository $dealRepository,
        private MeetingRepository $meetingRepository,
    ) {
    }

    #[Route('/stats', methods: ['GET'])]
    #[OA\Get(summary: 'Get dashboard statistics')]
    #[OA\Response(response: 200, description: 'Dashboard statistics')]
    public function stats(): JsonResponse
    {
        $allClients = $this->clientRepository->findAll();
        $clientsCount = 0;
        $prospectsCount = 0;
        $activeCount = 0;
        $demosCount = 0;

        foreach ($allClients as $client) {
            if ($client->getStatus() === 'deleted') {
                continue;
            }
            $clientsCount++;
            if ($client->getStatus() === 'prospect') {
                $prospectsCount++;
            }
            if ($client->getStatus() === 'active' || $client->getStatus() === 'implementing') {
                $activeCount++;
            }
            if ($client->getStatus() === 'demo') {
                $demosCount++;
            }
        }

        $deals = $this->dealRepository->findAll();
        $dealsCount = count($deals);
        $dealsValue = array_reduce($deals, fn(float $carry, $deal) => $carry + ($deal->getValue() ?? 0), 0.0);

        $upcomingMeetings = $this->meetingRepository->findUpcoming(5);

        $meetingsData = [];
        foreach ($upcomingMeetings as $meeting) {
            $meetingsData[] = [
                'id' => $meeting->getId(),
                'title' => $meeting->getTitle(),
                'startAt' => $meeting->getStartAt()?->format('c'),
                'endAt' => $meeting->getEndAt()?->format('c'),
                'status' => $meeting->getStatus(),
                'client' => $meeting->getClient() ? [
                    'id' => $meeting->getClient()->getId(),
                    'company_name' => $meeting->getClient()->getCompanyName(),
                    'contact_person' => $meeting->getClient()->getContactPerson(),
                ] : null,
            ];
        }

        return new JsonResponse([
            'clients_count' => $clientsCount,
            'prospects_count' => $prospectsCount,
            'active_count' => $activeCount,
            'demos_count' => $demosCount,
            'deals_count' => $dealsCount,
            'deals_value' => $dealsValue,
            'upcoming_meetings' => $meetingsData,
        ]);
    }
}
