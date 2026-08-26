<?php

namespace App\Controller\Web;

use App\Repository\TerminalRepository;
use App\Repository\TerminalSessionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class SessionStatusController extends AbstractController
{
    #[Route('/session/status', name: 'app_session_status', methods: ['GET'])]
    public function status(
        TerminalRepository $terminalRepo,
        TerminalSessionRepository $sessionRepo
    ): JsonResponse {
        $terminalCode = $_ENV['APP_TERMINAL_ID'] ?? 'TERMINAL-01';
        $terminal = $terminalRepo->findOneBy(['terminal_code' => $terminalCode, 'is_active' => true]);

        if (!$terminal) {
            return $this->json([
                'authenticated' => false,
                'terminal' => $terminalCode,
                'error' => 'Terminal configuration invalid'
            ]);
        }

        $session = $sessionRepo->findActiveSessionForTerminal($terminal->getId());

        if (!$session) {
            return $this->json([
                'authenticated' => false,
                'terminal' => $terminalCode
            ]);
        }

        return $this->json([
            'authenticated' => true,
            'display_name' => $session->getUser()->getDisplayName(),
            'role' => $session->getRole(),
            'expires_at' => $session->getExpiresAt()->format('c')
        ]);
    }
}
