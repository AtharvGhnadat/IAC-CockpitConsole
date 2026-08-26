<?php

namespace App\Controller\Web;

use App\Repository\TerminalRepository;
use App\Repository\TerminalSessionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LockScreenController extends AbstractController
{
    #[Route('/lock', name: 'app_lock_screen')]
    public function index(
        TerminalRepository $terminalRepo,
        TerminalSessionRepository $sessionRepo
    ): Response {
        $terminalCode = $_ENV['APP_TERMINAL_ID'] ?? 'TERMINAL-01';
        $terminal = $terminalRepo->findOneBy(['terminal_code' => $terminalCode, 'is_active' => true]);

        // If the terminal has an active session, auto-redirect to dashboard
        if ($terminal) {
            $session = $sessionRepo->findActiveSessionForTerminal($terminal->getId());
            if ($session) {
                return $this->redirectToRoute('app_dashboard');
            }
        }

        return $this->render('security/lock.html.twig', [
            'terminal_code' => $terminalCode,
            'terminal' => $terminal
        ]);
    }
}
