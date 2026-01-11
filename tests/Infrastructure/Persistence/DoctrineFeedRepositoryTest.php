<?php

namespace App\Tests\Infrastructure\Persistence;

use App\Domain\Entity\Feed;
use App\Domain\Enum\SourceEnum;
use App\Domain\Exception\Feed\FeedAlreadyExistsException;
use App\Domain\Repository\FeedRepositoryInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/* 
Para testear hacer commit con DAMA + die

\DAMA\DoctrineTestBundle\Doctrine\DBAL\StaticDriver::commit();
die;

Despues borrar la base de datos a mano
 */
class DoctrineFeedRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private FeedRepositoryInterface $repository;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $this->entityManager = $container->get('doctrine')->getManager();
        $this->repository = $container->get(FeedRepositoryInterface::class);
    }

    public function testSavePersistsFeedInDatabase(): void
    {
        // Generar una entidad válida para la prueba
        $feed = new Feed(
            title: 'Test Integration Title',
            url: 'https://test-example.com/integration',
            source: SourceEnum::EL_MUNDO,
            body: 'Contenido de prueba para persistencia',
            publishedAt: new \DateTimeImmutable(),
            image: 'https://test-example.com/integration.jpg'
        );

        // Persistir la entidad forzando el flush para escribir en base de datos
        $this->repository->save($feed, true);

        // Evitar que se guarde en memoria
        $this->entityManager->clear();

        // Recuperar la entidad usando el método específico del repositorio
        $savedFeed = $this->repository->findOneByUrlAndSource(
            'https://test-example.com/integration',
            SourceEnum::EL_MUNDO
        );

        // Verificar que los datos recuperados coinciden con los persistidos
        $this->assertNotNull($savedFeed);
        $this->assertInstanceOf(Feed::class, $savedFeed);
        $this->assertEquals('Test Integration Title', $savedFeed->getTitle());
        $this->assertEquals(SourceEnum::EL_MUNDO, $savedFeed->getSource());
        $this->assertEquals('Contenido de prueba para persistencia', $savedFeed->getBody());
        $this->assertEquals('https://test-example.com/integration.jpg', $savedFeed->getImage());
    }

    public function testCannotSaveDuplicateFeed(): void
    {
        // Crear y persistir la primera entidad
        $feed1 = new Feed(
            title: 'Original Feed',
            url: 'https://unique-check.com',
            source: SourceEnum::EL_MUNDO,
            body: 'Contenido original',
            publishedAt: new \DateTimeImmutable()
        );
        $this->repository->save($feed1, true);

        // Crear una segunda entidad con la misma URL y Fuente
        $feed2 = new Feed(
            title: 'Duplicate Feed',
            url: 'https://unique-check.com',
            source: SourceEnum::EL_MUNDO,
            body: 'Contenido duplicado',
            publishedAt: new \DateTimeImmutable()
        );

        // Se espera una excepción de base de datos al intentar guardar un duplicado source/url
        $this->expectException(FeedAlreadyExistsException::class);

        $this->repository->save($feed2, true);


    }

    public function testSoftDeletedFeedIsHiddenByDefault(): void
    {

        $feed = new Feed(
            'Titulo Vivo',
            'https://test.com/vivo',
            SourceEnum::EL_MUNDO,
            'Body',
            new \DateTimeImmutable()
        );
        $this->repository->save($feed, true);
        $id = $feed->getId();

        // Borrado
        $feed->softDelete();
        $this->repository->save($feed, true);

        // Limpiar memoria
        $this->entityManager->clear();

        // Buscar
        $result = $this->repository->findById($id);

        // Debe ser invisible
        $this->assertNull($result, 'El feed borrado debería ser invisible por defecto.');
    }

    public function testFindOneIncludingDeletedCanSeeDeletedFeed(): void
    {
        // Crear y borrar
        $feed = new Feed(
            'Titulo Fantasma',
            'https://test.com/fantasma',
            SourceEnum::EL_MUNDO,
            'Body',
            new \DateTimeImmutable()
        );
        $feed->softDelete();
        $this->repository->save($feed, true);

        $this->entityManager->clear();

        $deletedFeed = $this->repository->findOneByUrlAndSourceIncludingDeleted(
            'https://test.com/fantasma',
            SourceEnum::EL_MUNDO
        );

        // Debe encontrarlo
        $this->assertNotNull($deletedFeed, 'El método IncludingDeleted debería encontrar registros borrados.');
        $this->assertTrue($deletedFeed->isDeleted());
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
    }
}