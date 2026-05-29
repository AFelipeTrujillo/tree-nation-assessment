<?php

declare(strict_types=1);

namespace App\TreePlanting\Infrastructure\Http;

use App\TreePlanting\Application\RegisterVisit;
use DateTimeImmutable;
use InvalidArgumentException;
use JsonException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

final readonly class RegisterVisitAction
{
    public function __construct(
        private RegisterVisit $registerVisit,
        private CustomerStatsPresenter $presenter,
    ) {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $body = (string) $request->getBody();
            $payload = json_decode($body, true, flags: JSON_THROW_ON_ERROR);

            if (! is_array($payload)) {
                throw new InvalidArgumentException('Invalid JSON payload.');
            }

            $customerId = $this->requiredString($payload, 'customerId');
            $shopId = $this->requiredString($payload, 'shopId');
            $eventId = $this->optionalString($payload, 'eventId');
            $occurredAt = isset($payload['occurredAt'])
                ? new DateTimeImmutable((string) $payload['occurredAt'])
                : new DateTimeImmutable('now');

            $stats = ($this->registerVisit)($customerId, $shopId, $occurredAt, $eventId);

            return JsonResponse::write($response, $this->presenter->present($stats), 201);
        
        } catch (JsonException | InvalidArgumentException $exception) {
            return JsonResponse::write($response, ['error' => $exception->getMessage()], 400);
        } catch (Throwable $exception) {
            return JsonResponse::write($response, ['error' => 'Unexpected server error.'], 500);
        }
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function requiredString(array $payload, string $key): string
    {
        if (! isset($payload[$key]) || ! is_string($payload[$key]) || trim($payload[$key]) === '') {
            throw new InvalidArgumentException("Field '{$key}' is required and must be a non-empty string.");
        }

        return trim($payload[$key]);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function optionalString(array $payload, string $key): ?string
    {
        if (! isset($payload[$key])) {
            return null;
        }

        if (! is_string($payload[$key]) || trim($payload[$key]) === '') {
            throw new InvalidArgumentException("Field '{$key}' must be a non-empty string when provided.");
        }

        return trim($payload[$key]);
    }
}
