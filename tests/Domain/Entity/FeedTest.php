<?php

namespace App\Tests\Domain\Entity;

use App\Domain\Entity\Feed;
use App\Domain\Enum\SourceEnum;
use Faker\Factory;
use PHPUnit\Framework\TestCase;

class FeedTest extends TestCase
{
    private $faker;

    public function setUp(): void
    {
        $this->faker = Factory::create('es_ES');
    }
    public function testFeedCanBeCreatedWithValidData(): void
    {
        $title = $this->faker->sentence(8);
        $url = $this->faker->url();
        $body = $this->faker->text();
        $image = $this->faker->imageUrl();
        $publishedAt = new \DateTimeImmutable();

        $feed = new Feed(
            title: $title,
            url: $url,
            source: SourceEnum::EL_MUNDO,
            body: $body,
            publishedAt: $publishedAt,
            image: $image
        );

        $this->assertEquals($title, $feed->getTitle());
        $this->assertEquals($url, $feed->getUrl());
        $this->assertEquals(SourceEnum::EL_MUNDO, $feed->getSource());
        $this->assertEquals($body, $feed->getBody());
        $this->assertEquals($image, $feed->getImage());
        $this->assertInstanceOf(\DateTimeImmutable::class, $feed->getPublishedAt());
    }
}