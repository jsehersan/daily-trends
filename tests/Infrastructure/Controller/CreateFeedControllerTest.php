<?php

namespace App\Tests\Application\Controller\Api\Feed;

use App\Factory\FeedFactory;
use App\Domain\Enum\SourceEnum;
use Faker\Factory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;


class CreateFeedControllerTest extends WebTestCase
{
    use Factories, ResetDatabase;
    private $faker;

    public function setUp(): void
    {
        $this->faker = Factory::create('es_ES');
    }

    public function testItCreatesAFeedSuccessfully(): void
    {
        $client = static::createClient();

        $url = $this->faker->url();
        $payload = [
            'title' => 'Noticia de Última Hora',
            'url' => $url,
            'body' => 'Este contenido se ha enviado vía API.',
            'source' => SourceEnum::EL_MUNDO->value,
            'publishedAt' => '2026-01-12 12:00:00',
            'image' => 'https://example.com/foto.jpg'
        ];


        $client->request(
            'POST',
            '/api/v1/feeds/',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload)
        );

        $this->assertResponseStatusCodeSame(201); // Crea el feed
        $this->assertResponseHeaderSame('content-type', 'application/json');

        $response = json_decode($client->getResponse()->getContent(), true);

        // Comprobamos que devuelve el objeto creado
        $this->assertArrayHasKey('id', $response);
        $this->assertEquals('Noticia de Última Hora', $response['title']);

        // Revisamos db
        FeedFactory::repository()->assert()->count(1);
        FeedFactory::repository()->assert()->exists(['url' => $url]);
    }

    public function testItReturnsErrorOnInvalidData(): void
    {
        $client = static::createClient();

        // Payload incompleto
        $invalidPayload = [
            'title' => 'Titulo incompleto',
            'body' => 'body pero faltan más datos'
        ];

        $client->request(
            'POST',
            '/api/v1/feeds/',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($invalidPayload)
        );

        // Estamos devolviendo 400 en nuestro ApiExceptionListener
        $this->assertResponseStatusCodeSame(400);
    }

    public function testItPreventsDuplicateFeeds(): void
    {
        $client = static::createClient();

        // Creamos una noticia previa en BD
        FeedFactory::createOne([
            'url' => 'https://duplicada.com',
            'source' => SourceEnum::MANUAL
        ]);

        // Intentamos crear una con el mismo url, el source desde aqui siempre es Manual
        $payload = [
            'title' => 'Intento de duplicado',
            'url' => 'https://duplicada.com',
            'body' => 'Esto debería fallar',
            'publishedAt' => '2026-01-01 00:00:00'
        ];

        $client->request(
            'POST',
            '/api/v1/feeds/',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload)
        );


        //Esperamos un 409 como digimos en FeedAlreadyExistsException
        $this->assertResponseStatusCodeSame(409);
    }
}