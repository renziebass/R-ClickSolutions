<?php
declare(strict_types=1);

namespace Mariah\Core;

final class Paginator
{
    public const DEFAULT_PER_PAGE = 20;
    public const MAX_PER_PAGE     = 100;

    public function __construct(
        public readonly int $page,
        public readonly int $perPage
    ) {}

    public static function fromRequest(Request $r): self
    {
        $page    = max(1, $r->qInt('page', 1));
        $perPage = $r->qInt('per_page', self::DEFAULT_PER_PAGE);
        $perPage = max(1, min(self::MAX_PER_PAGE, $perPage));

        return new self($page, $perPage);
    }

    public function offset(): int
    {
        return ($this->page - 1) * $this->perPage;
    }

    public function meta(int $total): array
    {
        $lastPage = $total === 0 ? 1 : (int) ceil($total / $this->perPage);

        return [
            'page'      => $this->page,
            'per_page'  => $this->perPage,
            'total'     => $total,
            'last_page' => $lastPage,
            'from'      => $total === 0 ? 0 : $this->offset() + 1,
            'to'        => min($this->offset() + $this->perPage, $total),
        ];
    }
}
