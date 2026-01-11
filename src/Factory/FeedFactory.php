<?php

namespace App\Factory;

use App\Domain\Entity\Feed;
use App\Domain\Enum\SourceEnum;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Feed>
 */
final class FeedFactory extends PersistentProxyObjectFactory
{
    public static function class(): string
    {
        return Feed::class;
    }

    protected function defaults(): array|callable
    {
        return [
            'title' => self::faker()->sentence(6),
            'body' => self::faker()->paragraphs(3, true),
            'url' => self::faker()->unique()->url(),
            'source' => self::faker()->randomElement(SourceEnum::cases()),
            'image' => self::faker()->imageUrl(640, 480, 'news'),
            'publishedAt' => \DateTimeImmutable::createFromMutable(self::faker()->dateTimeThisYear()),
        ];
    }
}