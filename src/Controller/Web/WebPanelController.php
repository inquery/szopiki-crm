<?php

namespace App\Controller\Web;

use App\Repository\PanelConfigRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class WebPanelController extends AbstractController
{
    public function __construct(
        private PanelConfigRepository $panelConfigRepository,
    ) {}

    #[Route('/panel', name: 'panel_list')]
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $panels = $this->panelConfigRepository->findAll();

        return $this->render('panel/index.html.twig', [
            'panels' => $panels,
        ]);
    }
}
