<?php

namespace Tests;

use Orchestra\Testbench\Concerns\WithWorkbench;
use Patressz\Essentials\EssentialsServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    use WithWorkbench;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Define environment setup.
     */
    public function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');
    }

    /**
     * Get package providers.
     *
     * @return array<int, class-string<ServiceProvider>>
     */
    protected function getPackageProviders($app): array
    {
        return [
            EssentialsServiceProvider::class,
        ];
    }
}
