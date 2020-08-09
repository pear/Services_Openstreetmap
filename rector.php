<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set(
        Option::AUTOLOAD_PATHS,
        [
        __DIR__ . '/vendor/autoload.php',
        __DIR__ . '/Services/OpenStreetMap/',
        ]
    );

    $parameters->set(
        Option::EXCLUDE_PATHS,
        [ __DIR__ . '/vendor/*', ]
    );
};
