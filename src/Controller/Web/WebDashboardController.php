<?php

namespace App\Controller\Web;

use App\Repository\ClientRepository;
use App\Repository\DealRepository;
use App\Repository\MeetingRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class WebDashboardController extends AbstractController
{
    public function __construct(
        private ClientRepository $clientRepository,
        private DealRepository $dealRepository,
        private MeetingRepository $meetingRepository,
    ) {}

    #[Route('/', name: 'dashboard')]
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $allClients = $this->clientRepository->findAll();
        $prospectsCount = 0;
        $activeCount = 0;
        $demosCount = 0;

        foreach ($allClients as $client) {
            if ($client->getStatus() === 'deleted') continue;
            if ($client->getStatus() === 'prospect') $prospectsCount++;
            if ($client->getStatus() === 'active' || $client->getStatus() === 'implementing') $activeCount++;
            if ($client->getStatus() === 'demo') $demosCount++;
        }

        $deals = $this->dealRepository->findAll();
        $dealsValue = array_reduce($deals, fn(float $carry, $deal) => $carry + ($deal->getValue() ?? 0), 0.0);

        $upcomingMeetings = $this->meetingRepository->findUpcoming(5);

        return $this->render('dashboard/index.html.twig', [
            'prospects_count' => $prospectsCount,
            'demos_count' => $demosCount,
            'active_count' => $activeCount,
            'deals_value' => $dealsValue,
            'upcoming_meetings' => $upcomingMeetings,
        ]);
    }
}
