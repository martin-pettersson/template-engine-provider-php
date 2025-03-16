<?php

/*
 * Copyright (c) 2025 Martin Pettersson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N7e\Templating\Php;

use N7e\Configuration\ConfigurationInterface;
use N7e\DependencyInjection\ContainerBuilderInterface;
use N7e\DependencyInjection\ContainerInterface;
use N7e\RootDirectoryAggregateInterface;
use N7e\ServiceProviderInterface;
use N7e\TemplateEngineProviderInterface;
use N7e\Templating\Php;
use N7e\Templating\TemplateEngineInterface;
use Override;

/**
 * Provides a configured PHP template engine implementation.
 */
final class TemplateEngineProvider implements ServiceProviderInterface, TemplateEngineProviderInterface
{
    /**
     * Application root directory.
     *
     * @var string
     */
    private string $rootDirectory;

    #[Override]
    public function configure(ContainerBuilderInterface $containerBuilder): void
    {
        $container = $containerBuilder->build();

        /** @var \N7e\Collection\WritableCollectionInterface $templateEngineProviders */
        $templateEngineProviders = $container->get('template-engine-providers');

        $templateEngineProviders->add($this);

        /** @var \N7e\RootDirectoryAggregateInterface $rootDirectoryAggregate */
        $rootDirectoryAggregate = $container->get(RootDirectoryAggregateInterface::class);

        $this->rootDirectory = $rootDirectoryAggregate->getRootDirectory();
    }

    /**
     * {@inheritDoc}
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
     */
    #[Override]
    public function load(ContainerInterface $container): void
    {
    }

    #[Override]
    public function canProvideImplementationFor(string $templateEngine): bool
    {
        return strtolower($templateEngine) === 'php';
    }

    #[Override]
    public function createImplementationUsing(ConfigurationInterface $configuration): TemplateEngineInterface
    {
        /** @var string[] $templateDirectories */
        $templateDirectories = $configuration->get('templating.templateDirectories', []);

        return new Php\TemplateEngine(
            array_map(
                fn($directory) => $this->rootDirectory . '/' . ltrim($directory, '/'),
                $templateDirectories
            )
        );
    }
}
