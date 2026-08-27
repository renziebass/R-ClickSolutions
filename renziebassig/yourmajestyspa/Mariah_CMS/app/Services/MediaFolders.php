<?php
declare(strict_types=1);

namespace Mariah\Services;

/**
 * The fixed set of media library folders.
 *
 * Folders are declared in code, never created by an operator: a typo would
 * otherwise strand photos in a folder nothing else knows about, and the slug
 * doubles as a directory name under storage/uploads.
 *
 * Every content module that carries an image owns exactly one folder, so a
 * photo's folder answers "what is this picture for?" at a glance.
 */
final class MediaFolders
{
    public const UNSORTED = 'unsorted';

    /** @return array<string, string> slug => label, in display order */
    public static function all(): array
    {
        return [
            self::UNSORTED => 'Unsorted',
            'services'     => 'Services',
            'categories'   => 'Service Categories',
            'promotions'   => 'Promotions',
            'specials'     => 'Specials',
            'blog'         => 'Blog',
            'brands'       => 'Brands',
            'gift-cards'   => 'Gift Certificates',
            'products'     => 'Shop Products',
        ];
    }

    public static function isValid(?string $slug): bool
    {
        return $slug !== null && array_key_exists($slug, self::all());
    }

    /** Falls back to the slug itself so an unknown value still reads sensibly. */
    public static function label(?string $slug): string
    {
        return self::all()[$slug] ?? (string) $slug;
    }

    /** The slug if it is known, otherwise "unsorted". */
    public static function normalize(?string $slug): string
    {
        return self::isValid($slug) ? (string) $slug : self::UNSORTED;
    }
}
