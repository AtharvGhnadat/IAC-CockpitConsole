<?php

declare(strict_types=1);

namespace App\Controller\Web;

use App\Entity\AuditEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SessionController extends AbstractController
{
    #[Route('/session/lock', name: 'app_session_lock', methods: ['POST'])]
    public function lock(Request $request, EntityManagerInterface $em): RedirectResponse
    {
        // TerminalAuthorizationListener ensures these attributes exist if we reached here
        $session = $request->attributes->get('_terminal_session');
        $terminal = $request->attributes->get('_terminal');

        if ($session) {
            $session->setStatus('locked');
            $session->setEndReason('manual_lock');
            $session->setEndedAt(new \DateTimeImmutable());

            $audit = new AuditEvent();
            $audit->setAction('SESSION_LOCKED');
            $audit->setDescription('Manual dashboard lock by operator.');
            $audit->setContext([
                'session_uuid' => $session->getSessionUuid(),
                'terminal' => $terminal->getTerminalCode(),
            ]);

            $em->persist($session);
            $em->persist($audit);
            $em->flush();
        }

        return $this->redirectToRoute('app_lock_screen');
    }
}
