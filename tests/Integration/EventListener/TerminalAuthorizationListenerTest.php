<?php

namespace App\Tests\Integration\EventListener;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TerminalAuthorizationListenerTest extends WebTestCase
{
    public function testUnauthenticatedAccessRedirectsToLock()
    {
        $client = static::createClient();

        // Access protected route
        $client->request('GET', '/dashboard');

        // Should redirect to lock screen
        $this->assertResponseRedirects('/lock');
    }

    public function testLockScreenIsAccessible()
    {
        $client = static::createClient();

        // Access lock screen
        $client->request('GET', '/lock');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'CockpitConsole');
    }

    public function testSessionStatusIsAccessible()
    {
        $client = static::createClient();

        $client->request('GET', '/session/status');

        $this->assertResponseIsSuccessful();
        
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('authenticated', $response);
    }
}
