<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Repository\TerminalRepository;
use App\Repository\TerminalSessionRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;

#[AsEventListener(event: KernelEvents::REQUEST, method: 'onKernelRequest', priority: 0)]
class TerminalAuthorizationListener
{
    private TerminalSessionRepository $sessionRepo;
    private TerminalRepository $terminalRepo;
    private RouterInterface $router;
    private LoggerInterface $logger;
    private string $terminalCode;

    public function __construct(
        TerminalSessionRepository $sessionRepo,
        TerminalRepository $terminalRepo,
        RouterInterface $router,
        LoggerInterface $logger,
    ) {
        $this->sessionRepo = $sessionRepo;
        $this->terminalRepo = $terminalRepo;
        $this->router = $router;
        $this->logger = $logger;
        // Read terminal identity from env
        $this->terminalCode = $_ENV['APP_TERMINAL_ID'] ?? 'TERMINAL-01';
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $path = $request->getPathInfo();

        // We only protect the dashboard and session lock/status endpoints for now
        // But /session/status is for the lock screen to poll, so it should be accessible.
        // /lock is public.
        // For this phase, any route starting with /dashboard is protected.
        // We also protect /session/lock (so you can only lock if logged in).
        if (!str_starts_with($path, '/dashboard') && $path !== '/session/lock') {
            return;
        }

        // Validate Terminal Identity
        $terminal = $this->terminalRepo->findOneBy(['terminal_code' => $this->terminalCode, 'is_active' => true]);
        if (!$terminal) {
            $this->logger->error('Protected route accessed but server terminal identity is invalid or inactive.', [
                'terminal_code' => $this->terminalCode,
            ]);
            $this->redirectToLock($event);

            return;
        }

        // Validate Active Session
        $session = $this->sessionRepo->findActiveSessionForTerminal($terminal->getId());

        if (!$session) {
            // No active session, redirect to lock
            $this->redirectToLock($event);

            return;
        }

        // Also check if user is active
        $user = $session->getUser();
        if (!$user->isActive()) {
            $this->logger->warning('User active state changed while session active. Denying access.', [
                'session_uuid' => $session->getSessionUuid(),
                'user' => $user->getUsername(),
            ]);
            $this->redirectToLock($event);

            return;
        }

        // If valid, inject the session info into request attributes so controllers can use it
        $request->attributes->set('_terminal_session', $session);
        $request->attributes->set('_terminal', $terminal);
    }

    private function redirectToLock(RequestEvent $event): void
    {
        // Don't redirect if we are already on /lock
        if ($event->getRequest()->getPathInfo() === '/lock') {
            return;
        }
        $url = $this->router->generate('app_lock_screen');
        $event->setResponse(new RedirectResponse($url));
    }
}
