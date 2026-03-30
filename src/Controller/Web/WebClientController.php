<?php

namespace App\Controller\Web;

use App\Entity\Client;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class WebClientController extends AbstractController
{
    public function __construct(
        private ClientRepository $clientRepository,
        private EntityManagerInterface $entityManager,
    ) {}

    #[Route('/clients', name: 'client_list')]
    public function list(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $status = $request->query->get('status');
        $search = $request->query->get('search');
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = 25;

        $filters = [];
        if ($status) {
            if ($status === 'clients') {
                $filters['status'] = ['active', 'implementing'];
            } else {
                $filters['status'] = $status;
            }
        }
        if ($search) {
            $filters['search'] = $search;
        }

        $result = $this->clientRepository->findFiltered($filters, $page, $limit);

        return $this->render('clients/list.html.twig', [
            'clients' => $result['data'],
            'page' => $result['page'],
            'limit' => $result['limit'],
            'total' => $result['total'],
            'pages' => (int) ceil($result['total'] / $result['limit']),
            'current_status' => $status,
            'search' => $search,
        ]);
    }

    #[Route('/clients/new', name: 'client_new')]
    public function new(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        return $this->render('clients/form.html.twig', [
            'client' => null,
            'is_edit' => false,
        ]);
    }

    #[Route('/clients/{id}', name: 'client_show', requirements: ['id' => '\d+'])]
    public function show(int $id): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $client = $this->clientRepository->find($id);
        if (!$client) {
            throw $this->createNotFoundException('Klient nie znaleziony');
        }

        return $this->render('clients/detail.html.twig', [
            'client' => $client,
            'deals' => $client->getDeals(),
            'notes' => $client->getNoteEntries(),
            'meetings' => $client->getMeetings(),
        ]);
    }

    #[Route('/clients/{id}/edit', name: 'client_edit', requirements: ['id' => '\d+'])]
    public function edit(int $id): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $client = $this->clientRepository->find($id);
        if (!$client) {
            throw $this->createNotFoundException('Klient nie znaleziony');
        }

        return $this->render('clients/form.html.twig', [
            'client' => $client,
            'is_edit' => true,
        ]);
    }
}
