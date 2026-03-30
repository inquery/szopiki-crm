<?php

namespace App\Controller\Web;

use App\Repository\ClientRepository;
use App\Repository\NoteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class WebNoteController extends AbstractController
{
    public function __construct(
        private NoteRepository $noteRepository,
        private ClientRepository $clientRepository,
    ) {}

    #[Route('/notes', name: 'note_list')]
    public function list(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $notes = $this->noteRepository->findBy([], ['createdAt' => 'DESC']);
        $clients = $this->clientRepository->findAll();

        return $this->render('notes/list.html.twig', [
            'notes' => $notes,
            'clients' => $clients,
        ]);
    }
}
