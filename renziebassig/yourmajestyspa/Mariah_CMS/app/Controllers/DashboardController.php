<?php
declare(strict_types=1);

namespace Mariah\Controllers;

use Mariah\Core\Auth;
use Mariah\Core\Request;
use Mariah\Core\Response;
use Mariah\Repositories\AuditLogRepository;
use Mariah\Repositories\GiftCardRepository;
use Mariah\Repositories\MediaRepository;
use Mariah\Repositories\ProductRepository;
use Mariah\Repositories\PromotionRepository;
use Mariah\Repositories\ServiceRepository;
use Mariah\Repositories\SpecialRepository;
use Mariah\Repositories\UserRepository;

final class DashboardController
{
    /**
     * Every card and list on the dashboard home, in one round trip.
     * Sections the signed-in role cannot see are simply omitted.
     */
    public function stats(Request $request): never
    {
        $payload = [
            'services'   => (new ServiceRepository())->stats(),
            'promotions' => (new PromotionRepository())->stats(),
            'specials'   => (new SpecialRepository())->stats(),
            'products'   => (new ProductRepository())->stats(),
            'gift_cards' => (new GiftCardRepository())->stats(),
            'media'      => (new MediaRepository())->stats(),
        ];

        $payload['recent_services'] = Auth::can('services.view')
            ? (new ServiceRepository())->recentlyUpdated(5)
            : [];

        $payload['recent_promotions'] = Auth::can('promotions.view')
            ? (new PromotionRepository())->recent(5)
            : [];

        $payload['users'] = Auth::can('users.view')
            ? (new UserRepository())->stats()
            : null;

        $payload['recent_activity'] = Auth::can('audit_logs.view')
            ? (new AuditLogRepository())->recent(8)
            : [];

        Response::json($payload);
    }
}
