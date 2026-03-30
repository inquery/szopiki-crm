<?php

namespace App\Controller\Web;

use App\Repository\ClientRepository;
use App\Repository\DealRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class WebDealController extends AbstractController
{
    public function __construct(
        private DealRepository $dealRepository,
        private ClientRepository $clientRepository,
    ) {}

    #[Route('/deals', name: 'deal_list')]
    public function list(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $search = $request->query->get('search');
        $filters = [];
        if ($search) $filters['search'] = $search;

        $deals = $this->dealRepository->findFiltered($filters);

        return $this->render('deals/list.html.twig', [
            'deals' => $deals,
            'search' => $search,
        ]);
    }

    #[Route('/deals/pipeline', name: 'deal_pipeline')]
    public function pipeline(): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $deals = $this->dealRepository->findAll();
        $pipeline = [];
        foreach ($deals as $deal) {
            $pipeline[$deal->getStage()][] = $deal;
        }

        return $this->render('deals/pipeline.html.twig', [
            'pipeline' => $pipeline,
        ]);
    }

    #[Route('/deals/new', name: 'deal_new')]
    public function new(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $clients = $this->clientRepository->findAll();
        $clientId = $request->query->get('client_id');

        return $this->render('deals/form.html.twig', [
            'deal' => null,
            'is_edit' => false,
            'clients' => $clients,
            'preselected_client_id' => $clientId,
        ]);
    }

    #[Route('/deals/{id}', name: 'deal_show', requirements: ['id' => '\d+'])]
    public function show(int $id): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $deal = $this->dealRepository->find($id);
        if (!$deal) throw $this->createNotFoundException('Umowa nie znaleziona');

        return $this->render('deals/detail.html.twig', ['deal' => $deal]);
    }

    #[Route('/deals/{id}/edit', name: 'deal_edit', requirements: ['id' => '\d+'])]
    public function edit(int $id): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $deal = $this->dealRepository->find($id);
        if (!$deal) throw $this->createNotFoundException('Umowa nie znaleziona');

        $clients = $this->clientRepository->findAll();

        return $this->render('deals/form.html.twig', [
            'deal' => $deal,
            'is_edit' => true,
            'clients' => $clients,
            'preselected_client_id' => $deal->getClient()?->getId(),
        ]);
    }
}
