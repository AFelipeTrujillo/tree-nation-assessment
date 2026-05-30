<?php

declare(strict_types=1);

use App\Shared\Database\PdoConnectionFactory;
use App\TreePlanting\Application\GetCustomerStats;
use App\TreePlanting\Application\GetVisitsPerHour;
use App\TreePlanting\Application\RegisterVisit;
use App\TreePlanting\Domain\CustomerStatsRepository;
use App\TreePlanting\Domain\TreePlantingPolicy;
use App\TreePlanting\Domain\VisitAnalyticsRepository;
use App\TreePlanting\Domain\VisitRepository;
use App\TreePlanting\Infrastructure\Http\CustomerStatsPresenter;
use App\TreePlanting\Infrastructure\Http\GetCustomerStatsAction;
use App\TreePlanting\Infrastructure\Http\GetVisitsPerHourAction;
use App\TreePlanting\Infrastructure\Http\HealthAction;
use App\TreePlanting\Infrastructure\Http\RegisterVisitAction;
use App\TreePlanting\Infrastructure\Persistence\PdoCustomerStatsRepository;
use App\TreePlanting\Infrastructure\Persistence\PdoVisitAnalyticsRepository;
use App\TreePlanting\Infrastructure\Persistence\PdoVisitRepository;
use DI\ContainerBuilder;
use Dotenv\Dotenv;
use Psr\Container\ContainerInterface;
use Slim\Factory\AppFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require __DIR__ . '/../vendor/autoload.php';

$root = dirname(__DIR__);

if (file_exists($root . '/.env')) {
    Dotenv::createImmutable($root)->safeLoad();
}

$containerBuilder = new ContainerBuilder();

/**
 * Entry point for the Tree Nation Assessment application.
 * 
 * This script is responsible for initializing the application
 * and handling incoming HTTP requests. It is located in the 
 * public directory and serves as the main access point for 
 * the application.
 * 
 */
// 
$containerBuilder->addDefinitions([
    PDO::class => static function (): PDO {
        $root = dirname(__DIR__);
        $databasePath = $_ENV['DATABASE_PATH'] ?? 'var/app.sqlite';
        $absoluteDatabasePath = str_starts_with($databasePath, '/')
            ? $databasePath
            : $root . '/' . $databasePath;

        return (new PdoConnectionFactory($absoluteDatabasePath))->create();
    },

    TreePlantingPolicy::class => static function (): TreePlantingPolicy {
        return new TreePlantingPolicy((int) ($_ENV['VISITS_PER_TREE'] ?? 5));
    },

    VisitRepository::class => static fn (ContainerInterface $container): VisitRepository =>
        new PdoVisitRepository($container->get(PDO::class)),

    CustomerStatsRepository::class => static fn (ContainerInterface $container): CustomerStatsRepository =>
        new PdoCustomerStatsRepository($container->get(PDO::class)),

    VisitAnalyticsRepository::class => static fn (ContainerInterface $container): VisitAnalyticsRepository =>
        new PdoVisitAnalyticsRepository($container->get(PDO::class)),

    RegisterVisit::class => static fn (ContainerInterface $container): RegisterVisit =>
        new RegisterVisit(
            $container->get(VisitRepository::class),
            $container->get(CustomerStatsRepository::class),
            $container->get(TreePlantingPolicy::class),
        ),

    GetCustomerStats::class => static fn (ContainerInterface $container): GetCustomerStats =>
        new GetCustomerStats($container->get(CustomerStatsRepository::class)),

    GetVisitsPerHour::class => static fn (ContainerInterface $container): GetVisitsPerHour =>
        new GetVisitsPerHour($container->get(VisitAnalyticsRepository::class)),

    CustomerStatsPresenter::class => static fn (ContainerInterface $container): CustomerStatsPresenter =>
        new CustomerStatsPresenter($container->get(TreePlantingPolicy::class)),
]);

$container = $containerBuilder->build();
AppFactory::setContainer($container);

$app = AppFactory::create();
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();

$errorMiddleware = $app->addErrorMiddleware(
    displayErrorDetails: ($_ENV['APP_ENV'] ?? 'dev') === 'dev',
    logErrors: true,
    logErrorDetails: true,
);

$app->get('/', function(Request $request, Response $response): Response {
    $html = file_get_contents(__DIR__ . '/../templates/home.html');
    $response->getBody()->write($html);
    return $response->withHeader('Content-Type', 'text/html');
});

$app->get('/api/health', HealthAction::class);
$app->post('/api/visits', RegisterVisitAction::class);
$app->get('/api/customers/{customerId}/stats', GetCustomerStatsAction::class);
$app->get('/api/analytics/visits-per-hour', GetVisitsPerHourAction::class);

$app->run();
