<?php

namespace App\Tests\Application\Controller\Api\Feed;

use App\Factory\FeedFactory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class DeleteFeedControllerTest extends WebTestCase
{
    use Factories, ResetDatabase;


    public function testItSoftDeletesAFeed(): void
    {
        $client = static::createClient();
        //Creamos un feed
        $feed = FeedFactory::createOne();

        //Comprobamos que existe
        FeedFactory::repository()->assert()->count(1);

        //El delete es un soft delete, por lo que no se borra físicamente
        $client->request('DELETE', '/api/v1/feeds/' . $feed->getId());


        $this->assertResponseStatusCodeSame(204);
        //Comprobamos que sigue existiendo
        FeedFactory::repository()->assert()->count(0);
    }

    public function testItReturnsNotFoundIfFeedDoesNotExist(): void
    {
        $client = static::createClient();

        // ID UUID válido pero inventado
        $fakeId = '123e4567-e89b-12d3-a456-426614174000';

        $client->request('DELETE', '/api/v1/feeds/' . $fakeId);

        //Simplemente no hace nada porque no existe
        $this->assertResponseStatusCodeSame(404);
    }


    public function testItReturnsNotFoundIfTryingToDeleteAlreadyDeletedFeed(): void
    {
        $client = static::createClient();

        //Creamos, borramos y guardamos el feed
        $feed = FeedFactory::createOne();
        $feed->softDelete();
        $feed->_save();

        //Intentamos borrarla vía API
        $client->request('DELETE', '/api/v1/feeds/' . $feed->getId() . '/');

        //Como el filtro softdelete la oculta, el controlador no la encuentra y retorna un 404
        $this->assertResponseStatusCodeSame(404);
    }
}