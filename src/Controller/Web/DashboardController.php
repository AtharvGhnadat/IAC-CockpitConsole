<?php

declare(strict_types=1);

namespace App\Controller\Web;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(Request $request): Response
    {
        // Injected by TerminalAuthorizationListener
        $session = $request->attributes->get('_terminal_session');
        $terminal = $request->attributes->get('_terminal');

        return $this->render('dashboard/index.html.twig', [
            'session' => $session,
            'terminal' => $terminal,
        ]);
    }
}
