<?php

namespace App\Tests\Application\Controller\Api\Feed;

use App\Factory\FeedFactory;
use App\Domain\Enum\SourceEnum;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class ListFeedControllerTest extends WebTestCase
{
    use Factories, ResetDatabase;

    //Revisamos que viene bien la estructura de la respuesta, paginado e items
    public function testItReturnsPaginatedFeedsWithCorrectStructure(): void
    {
        $client = static::createClient();

        // Creamos 15 noticias aleatorias
        FeedFactory::createMany(15);

        // Pedimos primera página con 5 de límite
        $client->request('GET', '/api/v1/feeds/', [
            'page' => 1,
            'limit' => 5
        ]);

        // Comprobamos la estructura
        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);


        $this->assertArrayHasKey('items', $data);
        $this->assertArrayHasKey('totalItems', $data);
        $this->assertEquals(15, $data['totalItems']); // El total real en BD
        $this->assertCount(5, $data['items']);   // Lo que ha venido en esta página
    }

    public function testItReturnsFeedsOrderedByDateDesc(): void
    {
        $client = static::createClient();

        // Creamos una vieja
        FeedFactory::createOne(['publishedAt' => new \DateTimeImmutable('-10 days')]);
        // Creamos una nueva , debe salir la primera
        $newFeed = FeedFactory::createOne(['title' => 'Noticia Reciente', 'publishedAt' => new \DateTimeImmutable('now')]);

        $client->request('GET', '/api/v1/feeds/', [
            'sort_by' => 'publishedAt',
            'sort_order' => 'desc'
        ]);

        $data = json_decode($client->getResponse()->getContent(), true);
        $firstItem = $data['items'][0];
        //Comprobamos que la primera es la reciente
        $this->assertEquals('Noticia Reciente', $firstItem['title']);
        $this->assertEquals($newFeed->getId(), $firstItem['id']);
    }
}