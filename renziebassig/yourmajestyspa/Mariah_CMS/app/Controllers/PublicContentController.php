<?php
declare(strict_types=1);

namespace Mariah\Controllers;

use Mariah\Core\Database;
use Mariah\Core\HttpException;
use Mariah\Core\Request;
use Mariah\Core\Response;
use Mariah\Repositories\ServiceRepository;
use Mariah\Services\ScheduleResolver;

/**
 * Unauthenticated read-only endpoints for the public spa website.
 *
 * Every query filters on active/published AND deleted_at IS NULL, so a
 * deactivated or deleted record disappears from the site immediately while
 * remaining fully visible in the admin dashboard.
 *
 * Nothing here exposes ids of staff, timestamps of edits, or draft content.
 */
final class PublicContentController
{
    /** One aggregated payload for the whole page — a single round trip. */
    public function bootstrap(Request $request): never
    {
        $categories = $this->fetchCategories();
        $services   = $this->fetchServices();

        // Group services under their category so the page can build its tabs.
        $byCategory = [];
        foreach ($services as $service) {
            $byCategory[$service['category_slug']][] = $service;
        }

        foreach ($categories as $i => $category) {
            $categories[$i]['services'] = $byCategory[$category['slug']] ?? [];
        }

        // Only categories that actually have live services become tabs.
        $categories = array_values(array_filter(
            $categories,
            static fn (array $c): bool => $c['services'] !== []
        ));

        Response::json([
            'categories'         => $categories,
            'services'           => $services,
            'most_loved'         => $this->fetchMostLoved(),
            'specials'           => $this->fetchSpecials(),
            'promotions'         => $this->fetchPromotions(),
            'gift_cards'         => $this->fetchGiftCards('gift_card'),
            'memberships'        => $this->fetchGiftCards('membership'),
            'brands'             => $this->fetchBrands(),
            'product_categories' => $this->fetchProductCategories(),
            'products'           => $this->fetchProducts(),
            'generated_at'       => date('c'),
        ]);
    }

    public function services(Request $request): never
    {
        Response::json($this->fetchServices(
            $request->q('category') !== null ? (string) $request->q('category') : null
        ));
    }

    public function service(Request $request, array $args): never
    {
        $slug = (string) ($args['slug'] ?? '');

        $row = Database::fetchOne(
            "SELECT s.id, s.name, s.slug, s.short_description, s.description,
                    s.price, s.price_display, s.promo_price,
                    s.duration_minutes, s.duration_display,
                    s.icon_key, s.booking_url, s.featured, s.most_loved_rank,
                    c.name AS category_name, c.slug AS category_slug,
                    m.file_url AS image_url, m.alt_text AS image_alt
               FROM services s
               JOIN service_categories c ON c.id = s.category_id
                    AND c.status = 'active' AND c.deleted_at IS NULL
               LEFT JOIN media m ON m.id = s.media_id AND m.deleted_at IS NULL
              WHERE s.slug = ? AND s.status = 'active' AND s.deleted_at IS NULL
              LIMIT 1",
            [$slug]
        );

        if ($row === null) {
            throw HttpException::notFound('That service is not available.');
        }

        $row = $this->decorateService($row);

        $row['gallery'] = Database::fetchAll(
            'SELECT m.file_url, COALESCE(si.alt_text, m.alt_text) AS alt_text
               FROM service_images si
               JOIN media m ON m.id = si.media_id AND m.deleted_at IS NULL
              WHERE si.service_id = ?
              ORDER BY si.is_primary DESC, si.display_order ASC',
            [(int) $row['id']]
        );

        Response::json($row);
    }

    public function categories(Request $request): never
    {
        Response::json($this->fetchCategories());
    }

    public function specials(Request $request): never
    {
        Response::json($this->fetchSpecials());
    }

    public function promotions(Request $request): never
    {
        Response::json($this->fetchPromotions());
    }

    public function products(Request $request): never
    {
        Response::json($this->fetchProducts(
            $request->q('category') !== null ? (string) $request->q('category') : null
        ));
    }

    public function productCategories(Request $request): never
    {
        Response::json($this->fetchProductCategories());
    }

    public function brands(Request $request): never
    {
        Response::json($this->fetchBrands());
    }

    public function giftCards(Request $request): never
    {
        Response::json($this->fetchGiftCards(
            $request->q('type') !== null ? (string) $request->q('type') : null
        ));
    }

    // -----------------------------------------------------------------
    // Queries
    // -----------------------------------------------------------------

    private function fetchCategories(): array
    {
        $rows = Database::fetchAll(
            "SELECT c.id, c.name, c.slug, c.description, c.icon_key,
                    m.file_url AS image_url
               FROM service_categories c
               LEFT JOIN media m ON m.id = c.media_id AND m.deleted_at IS NULL
              WHERE c.status = 'active' AND c.deleted_at IS NULL
              ORDER BY c.display_order ASC, c.name ASC"
        );

        return array_map(static function (array $r): array {
            $r['id'] = (int) $r['id'];
            return $r;
        }, $rows);
    }

    private function fetchServices(?string $categorySlug = null): array
    {
        $sql = "SELECT s.id, s.name, s.slug, s.short_description, s.description,
                       s.price, s.price_display, s.promo_price,
                       s.duration_minutes, s.duration_display,
                       s.icon_key, s.booking_url, s.featured, s.most_loved_rank,
                       s.display_order,
                       c.name AS category_name, c.slug AS category_slug,
                       m.file_url AS image_url, m.alt_text AS image_alt
                  FROM services s
                  JOIN service_categories c ON c.id = s.category_id
                       AND c.status = 'active' AND c.deleted_at IS NULL
                  LEFT JOIN media m ON m.id = s.media_id AND m.deleted_at IS NULL
                 WHERE s.status = 'active' AND s.deleted_at IS NULL";

        $params = [];

        if ($categorySlug !== null && $categorySlug !== '') {
            $sql     .= ' AND c.slug = ?';
            $params[] = $categorySlug;
        }

        $sql .= ' ORDER BY c.display_order ASC, s.display_order ASC, s.name ASC';

        return array_map([$this, 'decorateService'], Database::fetchAll($sql, $params));
    }

    private function fetchMostLoved(): array
    {
        return array_map(
            [$this, 'decorateService'],
            Database::fetchAll(
                "SELECT s.id, s.name, s.slug, s.short_description, s.description,
                        s.price, s.price_display, s.duration_minutes, s.duration_display,
                        s.booking_url, s.most_loved_rank, s.icon_key,
                        c.name AS category_name, c.slug AS category_slug,
                        m.file_url AS image_url, m.alt_text AS image_alt
                   FROM services s
                   JOIN service_categories c ON c.id = s.category_id
                   LEFT JOIN media m ON m.id = s.media_id AND m.deleted_at IS NULL
                  WHERE s.most_loved_rank IS NOT NULL
                    AND s.status = 'active' AND s.deleted_at IS NULL
                  ORDER BY s.most_loved_rank ASC
                  LIMIT 3"
            )
        );
    }

    private function decorateService(array $r): array
    {
        $r['id']       = (int) $r['id'];
        $r['price']    = (float) $r['price'];
        $r['featured'] = (bool) ($r['featured'] ?? false);

        $r['price_label'] = $r['price_display']
            ?: ServiceRepository::formatMoney((float) $r['price']);

        $r['duration_label'] = $r['duration_display']
            ?: ($r['duration_minutes'] !== null ? $r['duration_minutes'] . ' min' : null);

        if (array_key_exists('promo_price', $r)) {
            $r['promo_price'] = $r['promo_price'] === null ? null : (float) $r['promo_price'];
            $r['promo_label'] = $r['promo_price'] === null
                ? null
                : ServiceRepository::formatMoney($r['promo_price']);
        }

        if (array_key_exists('most_loved_rank', $r) && $r['most_loved_rank'] !== null) {
            $r['most_loved_rank'] = (int) $r['most_loved_rank'];
        }

        unset($r['price_display'], $r['duration_display'], $r['display_order']);

        return $r;
    }

    private function fetchSpecials(): array
    {
        $where = ScheduleResolver::publicWhere('sp');

        $rows = Database::fetchAll(
            "SELECT sp.id, sp.title, sp.slug, sp.description, sp.badge_label,
                    sp.price, sp.price_display, sp.compare_at_price, sp.booking_url,
                    sp.featured, sp.end_date,
                    m.file_url AS image_url, m.alt_text AS image_alt
               FROM specials sp
               LEFT JOIN media m ON m.id = sp.media_id AND m.deleted_at IS NULL
              WHERE {$where}
              ORDER BY sp.display_order ASC, sp.id ASC"
        );

        return array_map(static function (array $r): array {
            $r['id']       = (int) $r['id'];
            $r['featured'] = (bool) $r['featured'];
            $r['price']    = $r['price'] === null ? null : (float) $r['price'];

            $r['price_label'] = $r['price_display']
                ?: ($r['price'] !== null ? ServiceRepository::formatMoney((float) $r['price']) : null);

            $r['compare_at_label'] = $r['compare_at_price'] === null
                ? null
                : ServiceRepository::formatMoney((float) $r['compare_at_price']);

            unset($r['price_display'], $r['compare_at_price']);

            return $r;
        }, $rows);
    }

    private function fetchPromotions(): array
    {
        $where = ScheduleResolver::publicWhere('p');

        $rows = Database::fetchAll(
            "SELECT p.id, p.title, p.slug, p.description, p.badge_label,
                    p.discount_type, p.discount_value, p.original_price, p.promo_price,
                    p.booking_url, p.featured, p.end_date,
                    m.file_url AS image_url, m.alt_text AS image_alt
               FROM promotions p
               LEFT JOIN media m ON m.id = p.media_id AND m.deleted_at IS NULL
              WHERE {$where}
              ORDER BY p.display_order ASC, p.id ASC"
        );

        return array_map(static function (array $r): array {
            $r['id']             = (int) $r['id'];
            $r['featured']       = (bool) $r['featured'];
            $r['discount_value'] = (float) $r['discount_value'];
            $r['discount_label'] = \Mariah\Repositories\PromotionRepository::discountLabel($r);

            $r['services'] = Database::fetchAll(
                "SELECT s.name, s.slug
                   FROM promotion_services ps
                   JOIN services s ON s.id = ps.service_id
                  WHERE ps.promotion_id = ? AND s.status = 'active' AND s.deleted_at IS NULL
                  ORDER BY s.display_order",
                [$r['id']]
            );

            return $r;
        }, $rows);
    }

    private function fetchGiftCards(?string $type = null): array
    {
        $sql = "SELECT g.id, g.type, g.title, g.slug, g.description,
                       g.price, g.price_display, g.price_interval,
                       g.purchase_url, g.badge_label, g.featured,
                       m.file_url AS image_url, m.alt_text AS image_alt
                  FROM gift_cards g
                  LEFT JOIN media m ON m.id = g.media_id AND m.deleted_at IS NULL
                 WHERE g.status = 'active' AND g.deleted_at IS NULL";

        $params = [];

        if ($type !== null && in_array($type, ['gift_card', 'membership'], true)) {
            $sql     .= ' AND g.type = ?';
            $params[] = $type;
        }

        $sql .= ' ORDER BY g.display_order ASC, g.id ASC';

        return array_map(static function (array $r): array {
            $r['id']       = (int) $r['id'];
            $r['featured'] = (bool) $r['featured'];
            $r['price']    = $r['price'] === null ? null : (float) $r['price'];

            $r['price_label'] = $r['price_display']
                ?: ($r['price'] !== null ? ServiceRepository::formatMoney((float) $r['price']) : null);

            unset($r['price_display']);

            return $r;
        }, Database::fetchAll($sql, $params));
    }

    private function fetchBrands(): array
    {
        return array_map(static function (array $r): array {
            $r['id'] = (int) $r['id'];
            return $r;
        }, Database::fetchAll(
            "SELECT b.id, b.name, b.slug, b.tagline, m.file_url AS image_url
               FROM product_brands b
               LEFT JOIN media m ON m.id = b.media_id AND m.deleted_at IS NULL
              WHERE b.status = 'active' AND b.deleted_at IS NULL
              ORDER BY b.display_order ASC, b.name ASC"
        ));
    }

    private function fetchProductCategories(): array
    {
        return array_map(static function (array $r): array {
            $r['id'] = (int) $r['id'];
            return $r;
        }, Database::fetchAll(
            "SELECT id, name, slug, description
               FROM product_categories
              WHERE status = 'active' AND deleted_at IS NULL
              ORDER BY display_order ASC, name ASC"
        ));
    }

    private function fetchProducts(?string $categorySlug = null): array
    {
        $sql = "SELECT p.id, p.name, p.slug, p.description, p.price, p.compare_at_price,
                       p.icon_key, p.badge_label, p.featured,
                       b.name AS brand_name, b.slug AS brand_slug,
                       pc.name AS category_name, pc.slug AS category_slug,
                       m.file_url AS image_url, m.alt_text AS image_alt
                  FROM products p
                  LEFT JOIN product_brands b      ON b.id  = p.brand_id    AND b.deleted_at IS NULL
                  LEFT JOIN product_categories pc ON pc.id = p.category_id AND pc.deleted_at IS NULL
                  LEFT JOIN media m               ON m.id  = p.media_id    AND m.deleted_at IS NULL
                 WHERE p.status = 'active' AND p.deleted_at IS NULL";

        $params = [];

        if ($categorySlug !== null && $categorySlug !== '') {
            $sql     .= ' AND pc.slug = ?';
            $params[] = $categorySlug;
        }

        $sql .= ' ORDER BY p.display_order ASC, p.name ASC';

        return array_map(static function (array $r): array {
            $r['id']          = (int) $r['id'];
            $r['featured']    = (bool) $r['featured'];
            $r['price']       = (float) $r['price'];
            $r['price_label'] = ServiceRepository::formatMoney((float) $r['price']);

            $r['compare_at_label'] = $r['compare_at_price'] === null
                ? null
                : ServiceRepository::formatMoney((float) $r['compare_at_price']);

            unset($r['compare_at_price']);

            return $r;
        }, Database::fetchAll($sql, $params));
    }
}
