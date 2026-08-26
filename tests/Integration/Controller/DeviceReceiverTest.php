<?php

namespace App\Tests\Integration\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DeviceReceiverTest extends WebTestCase
{
    public function testValidPlcIngestion()
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/device/plc',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'cockpit' => '2301AZ106071N',
                'dateTime' => '2026-08-25 18:25:22'
            ])
        );

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(201);
        
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue($response['success']);
        $this->assertArrayHasKey('event_uuid', $response);
    }

    public function testInvalidContentTypeIsRejected()
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/device/plc',
            [],
            [],
            ['CONTENT_TYPE' => 'application/x-www-form-urlencoded'], // Invalid
            'cockpit=123'
        );

        $this->assertResponseStatusCodeSame(415);
    }

    public function testMalformedJsonIsRejected()
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/device/plc',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{broken_json'
        );

        $this->assertResponseStatusCodeSame(400);
        
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertFalse($response['success']);
        $this->assertEquals('INVALID_PAYLOAD', $response['error']);
    }

    public function testLargePayloadIsRejected()
    {
        $client = static::createClient();

        // Generate 17KB string
        $largeString = str_repeat('a', 17000);
        
        $client->request(
            'POST',
            '/api/device/plc',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['cockpit' => $largeString, 'dateTime' => '2026-08-25 18:25:22'])
        );

        $this->assertResponseStatusCodeSame(413);
    }
}
