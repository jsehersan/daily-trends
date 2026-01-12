<?php

namespace App\Tests\Application\Controller\Api\Feed;

use App\Factory\FeedFactory;
use App\Domain\Enum\SourceEnum;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class UpdateFeedControllerTest extends WebTestCase
{
    use Factories, ResetDatabase;

    public function testItUpdatesAFeedSuccessfully(): void
    {
        $client = static::createClient();

        // Noticia original para actualizar
        $feed = FeedFactory::createOne([
            'title' => 'Título Original',
            'body' => 'Cuerpo Original',
            'url' => 'https://original.com/news',
            'source' => SourceEnum::MANUAL

        ]);

        // Actualizamos algunos datos
        $payload = [
            'title' => 'Título Actualizado',
            'body' => 'El cuerpo ha cambiado',
            'url' => 'https://actualizado.com/news',
            'publishedAt' => $feed->getPublishedAt()->format('Y-m-d H:i:s'),
            'image' => 'https://actualizado.com/img.jpg'
        ];

        $client->request(
            'PUT',
            '/api/v1/feeds/' . $feed->getId(),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload)
        );


        $this->assertResponseIsSuccessful(); // Esperamos 200 


        $this->assertEquals('Título Actualizado', $feed->getTitle());
        $this->assertEquals('El cuerpo ha cambiado', $feed->getBody());
        $this->assertEquals('https://actualizado.com/news', $feed->getUrl());

    }

    public function testItReturnsNotFoundForNonExistentFeed(): void
    {
        $client = static::createClient();

        // Payload válido
        $payload = [
            'title' => 'Inventado',
            'url' => 'https://inventado.com',
            'body' => 'content',
            'publishedAt' => '2026-01-01 00:00:00'
        ];

        // ID inventado (UUID válido pero inexistente)
        $fakeId = '123e4567-e89b-12d3-a456-426614174000';

        $client->request(
            'PUT',
            '/api/v1/feeds/' . $fakeId . '/',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload)
        );

        $this->assertResponseStatusCodeSame(404);
    }

    public function testItReturnsBadRequestOnInvalidData(): void
    {
        $client = static::createClient();
        $feed = FeedFactory::createOne();

        // Enviamos una URL inválida para probar validaciones
        $payload = [
            'title' => 'Titulo',
            'url' => 'esto-no-es-una-url', // Falla el requireTld: true
        ];

        $client->request(
            'PUT',
            '/api/v1/feeds/' . $feed->getId(),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload)
        );

        $this->assertResponseStatusCodeSame(422);
        $data = json_decode($client->getResponse()->getContent(), true);

        //Respuesta tipo de un error de validacion en mi dominio
        $this->assertEquals('error', $data['status']);
        $this->assertEquals('validation_error', $data['type']);

        //dd($data);
    }
}