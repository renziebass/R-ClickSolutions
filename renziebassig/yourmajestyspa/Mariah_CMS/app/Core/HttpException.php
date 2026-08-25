<?php
declare(strict_types=1);

namespace Mariah\Core;

/**
 * Any exception the client is allowed to see. Everything else becomes a
 * logged 500 with a generic message.
 */
class HttpException extends \RuntimeException
{
    public function __construct(
        private int $status,
        private string $errorCode,
        string $message,
        private array $fields = []
    ) {
        parent::__construct($message);
    }

    public function status(): int { return $this->status; }
    public function errorCode(): string { return $this->errorCode; }
    public function fields(): array { return $this->fields; }

    public static function badRequest(string $m = 'The request could not be understood.'): self
    {
        return new self(400, 'BAD_REQUEST', $m);
    }

    public static function unauthorized(string $m = 'You must sign in to continue.'): self
    {
        return new self(401, 'UNAUTHENTICATED', $m);
    }

    public static function forbidden(string $m = 'You do not have permission to perform this action.'): self
    {
        return new self(403, 'FORBIDDEN', $m);
    }

    public static function notFound(string $m = 'The requested record was not found.'): self
    {
        return new self(404, 'NOT_FOUND', $m);
    }

    public static function conflict(string $m = 'That record already exists.'): self
    {
        return new self(409, 'CONFLICT', $m);
    }

    public static function validation(array $fields, string $m = 'Please correct the highlighted fields.'): self
    {
        return new self(422, 'VALIDATION_FAILED', $m, $fields);
    }

    public static function tooManyRequests(string $m = 'Too many attempts. Please try again later.'): self
    {
        return new self(429, 'TOO_MANY_REQUESTS', $m);
    }
}
