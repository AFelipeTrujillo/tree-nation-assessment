<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Shared\Database\PdoConnectionFactory;
use App\TreePlanting\Application\GetCustomerStats;
use App\TreePlanting\Application\RegisterVisit;
use App\TreePlanting\Domain\CustomerStatsRepository;
use App\TreePlanting\Domain\TreePlantingPolicy;
use App\TreePlanting\Domain\VisitRepository;
use App\TreePlanting\Infrastructure\Http\CustomerStatsPresenter;
use App\TreePlanting\Infrastructure\Http\GetCustomerStatsAction;
use App\TreePlanting\Infrastructure\Http\RegisterVisitAction;
use App\TreePlanting\Infrastructure\Persistence\PdoCustomerStatsRepository;
use App\TreePlanting\Infrastructure\Persistence\PdoVisitRepository;
use DI\ContainerBuilder;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Slim\Factory\AppFactory;
use PDO;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Factory\StreamFactory;
final class VisitApiTest extends TestCase
{
    private const DATABASE_PATH = __DIR__ . '/../../var/test.sqlite';

    private App $app;
    protected function setUp(): void
    {
        // Fresh test database for each test
        if (file_exists(self::DATABASE_PATH)) {
            unlink(self::DATABASE_PATH);
        }

        $pdo = (new PdoConnectionFactory(self::DATABASE_PATH))->create();
        $this->initSchema($pdo);

        $containerBuilder = new ContainerBuilder();

        $containerBuilder->addDefinitions([
            PDO::class => fn (): PDO => $pdo,

            TreePlantingPolicy::class => fn (): TreePlantingPolicy => new TreePlantingPolicy(5),

            VisitRepository::class => fn (ContainerInterface $container): VisitRepository =>
                new PdoVisitRepository($container->get(PDO::class)),

            CustomerStatsRepository::class => fn (ContainerInterface $container): CustomerStatsRepository =>
                new PdoCustomerStatsRepository($container->get(PDO::class)),

            RegisterVisit::class => fn (ContainerInterface $container): RegisterVisit =>
                new RegisterVisit(
                    $container->get(VisitRepository::class),
                    $container->get(CustomerStatsRepository::class),
                    $container->get(TreePlantingPolicy::class),
                ),

            GetCustomerStats::class => fn (ContainerInterface $container): GetCustomerStats =>
                new GetCustomerStats($container->get(CustomerStatsRepository::class)),

            CustomerStatsPresenter::class => fn (ContainerInterface $container): CustomerStatsPresenter =>
                new CustomerStatsPresenter($container->get(TreePlantingPolicy::class)),
        ]);

        $container = $containerBuilder->build();
        AppFactory::setContainer($container);

        $this->app = AppFactory::create();
        $this->app->addBodyParsingMiddleware();
        $this->app->addRoutingMiddleware();

        $this->app->post('/api/visits', RegisterVisitAction::class);
        $this->app->get('/api/customers/{customerId}/stats', GetCustomerStatsAction::class);
    }

    protected function tearDown(): void
    {
        if (file_exists(self::DATABASE_PATH)) {
            unlink(self::DATABASE_PATH);
        }
    }

    public function testRegisterVisitsTriggersTreePlantingAtThreshold(): void
    {
        // Register 5 visits for the same customer (threshold = 5 visits per tree)
        for ($i = 1; $i <= 5; $i++) {
            $stats = $this->postVisit([
                'customerId' => 'cus_001',
                'shopId' => 'shop_001',
                'eventId' => "evt_$i",
                'occurredAt' => sprintf('2026-05-29T09:%02d:00Z', $i),
            ]);

            if ($i < 5) {
                self::assertSame(0, $stats['treesPlanted'], "Visit $i should not yet trigger a tree.");
            }
        }

        // Verify final stats via GET endpoint
        $response = $this->app->handle(
            $this->createJsonRequest('GET', '/api/customers/cus_001/stats')
        );

        self::assertSame(200, $response->getStatusCode());

        $body = $this->decodeResponse($response);
        self::assertSame('cus_001', $body['customerId']);
        self::assertSame(5, $body['totalVisits']);
        self::assertSame(1, $body['treesPlanted'], '5 visits with policy of 5 should equal 1 tree.');
        self::assertSame(0, $body['visitsUntilNextTree']);
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function postVisit(array $data): array
    {
        $response = $this->app->handle($this->createJsonRequest('POST', '/api/visits', $data));

        self::assertSame(201, $response->getStatusCode());

        return $this->decodeResponse($response);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function createJsonRequest(string $method, string $uri, array $data = []): ServerRequestInterface
    {
        $stream = (new StreamFactory())->createStream(
            $data !== [] ? json_encode($data, JSON_THROW_ON_ERROR) : ''
        );

        return (new ServerRequestFactory())
            ->createServerRequest($method, $uri)
            ->withHeader('Content-Type', 'application/json')
            ->withBody($stream);
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeResponse(ResponseInterface $response): array
    {
        return json_decode((string) $response->getBody(), true, flags: JSON_THROW_ON_ERROR);
    }

    private function initSchema(PDO $pdo): void
    {
        $schemaPath = dirname(__DIR__, 2) . '/database/schema.sql';
        $schema = file_get_contents($schemaPath);

        if ($schema === false) {
            throw new \RuntimeException('Could not read database/schema.sql');
        }

        $pdo->exec($schema);
    }
}

