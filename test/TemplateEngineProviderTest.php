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
use N7e\TemplateEngineProviderRegistryInterface;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

#[CoversClass(TemplateEngineProvider::class)]
class TemplateEngineProviderTest extends TestCase
{
    private TemplateEngineProvider $provider;
    private MockObject $containerBuilderMock;
    private MockObject $containerMock;
    private MockObject $registryMock;
    private MockObject $rootDirectoryAggregateMock;
    private MockObject $configurationMock;

    #[Before]
    public function setUp(): void
    {
        $this->provider = new TemplateEngineProvider();
        $this->containerBuilderMock = $this->getMockBuilder(ContainerBuilderInterface::class)->getMock();
        $this->containerMock = $this->getMockBuilder(ContainerInterface::class)->getMock();
        $this->registryMock = $this->getMockBuilder(TemplateEngineProviderRegistryInterface::class)->getMock();
        $this->rootDirectoryAggregateMock = $this->getMockBuilder(RootDirectoryAggregateInterface::class)->getMock();
        $this->configurationMock = $this->getMockBuilder(ConfigurationInterface::class)->getMock();

        $this->containerBuilderMock
            ->method('build')
            ->willReturn($this->containerMock);
        $this->containerMock
            ->method('get')
            ->willReturnOnConsecutiveCalls($this->registryMock, $this->rootDirectoryAggregateMock);
        $this->rootDirectoryAggregateMock
            ->method('getRootDirectory')
            ->willReturn('/root/directory');
    }

    #[Test]
    public function shouldIndicateAbleToProvidePhpTemplateEngineImplementation(): void
    {
        $this->assertTrue($this->provider->canProvideImplementationFor('php'));
        $this->assertTrue($this->provider->canProvideImplementationFor('Php'));
        $this->assertTrue($this->provider->canProvideImplementationFor('PHP'));
        $this->assertFalse($this->provider->canProvideImplementationFor('other'));
        $this->assertFalse($this->provider->canProvideImplementationFor(''));
    }

    #[Test]
    public function shouldProvideTemplateEngine(): void
    {
        $this->configurationMock
            ->expects($this->once())
            ->method('get')
            ->with('templating.templateDirectories')
            ->willReturn(['one', '/two']);
        $templateDirectoriesReflection = (new ReflectionClass(TemplateEngine::class))
            ->getProperty('templateDirectories');
        $templateDirectoriesReflection->setAccessible(true);

        $this->provider->configure($this->containerBuilderMock);
        $this->provider->load($this->containerMock);

        $templateEngine = $this->provider->createImplementationUsing($this->configurationMock);

        $this->assertEquals(
            [
                '/root/directory/one',
                '/root/directory/two'
            ],
            $templateDirectoriesReflection->getValue($templateEngine)
        );
    }
}
