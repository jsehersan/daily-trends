<?php

namespace App\Tests\Application\Controller\Api\Feed;

use App\Factory\FeedFactory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class GetFeedControllerTest extends WebTestCase
{
    use Factories, ResetDatabase;

    public function testItReturnsFeedDetailsSuccessfully(): void
    {
        $client = static::createClient();

        $feed = FeedFactory::createOne([
            'title' => 'Noticia en Detalle',
            'body' => 'Este es el cuerpo completo de la noticia',
            'url' => 'https://test.com/detail',
        ]);


        $client->request('GET', '/api/v1/feeds/' . $feed->getId());

        //Comprobamos que la respuesta es correcta
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');

        $data = json_decode($client->getResponse()->getContent(), true);


        $this->assertEquals($feed->getId(), $data['id']);

        // Comprobamos que el body es correcto
        $this->assertArrayHasKey('body', $data);
        $this->assertEquals(
            'Este es el cuerpo completo de la noticia',
            $data['body']
        );

        // También el titulo
        $this->assertEquals('Noticia en Detalle', $data['title']);
    }
    // Comprobamos un feed que no existe
    public function testItReturnsNotFoundForNonExistentId(): void
    {
        $client = static::createClient();

        // UUID random
        $randomId = '123e4567-e89b-12d3-a456-426614174000';

        $client->request('GET', '/api/v1/feeds/' . $randomId);

        $data = json_decode($client->getResponse()->getContent(), true);

        //Error que viene : {"status":"error","type":"business_error","code":404,"message":"Feed with ID \u0022123e4567-e89b-12d3-a456-426614174000\u0022 not found."}

        $this->assertResponseStatusCodeSame(404);
        $this->assertEquals('Feed with ID "123e4567-e89b-12d3-a456-426614174000" not found.', $data['message']);
    }


}