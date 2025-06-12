<?php

use Patressz\Essentials\Enums\ConfigureOption;

beforeEach(function () {
    $configurationFiles = [
        base_path('pint.json'),
        base_path('phpstan.neon'),
        base_path('rector.php'),
    ];

    foreach ($configurationFiles as $file) {
        if (file_exists($file)) {
            unlink($file);
        }
    }

    $appServiceProvider = app_path('Providers/AppServiceProvider.php');
    if (! file_exists($appServiceProvider)) {
        copy(__DIR__.'/Fixtures/AppServiceProvider.php', $appServiceProvider);
    }
});

it('installs essentials and configures the application correctly', function () {
    $configurableOptions = collect(ConfigureOption::cases())
        ->map(fn (ConfigureOption $option) => $option->value)
        ->toArray();

    $this->artisan('essentials:install -y')
        ->expectsQuestion('What would you like to install and configure?', [
            'laravel/pint',
            'larastan/larastan',
            'driftingly/rector-laravel',
        ])
        ->expectsConfirmation('Do you want to add basic configuration methods to the AppServiceProvider?', 'yes')
        ->expectsQuestion('Which configuration methods would you like to add?', $configurableOptions)
        ->expectsOutputToContain('Installation and configuration completed successfully!')
        ->assertExitCode(0);

    expect(file_exists(base_path('pint.json')))->toBeTrue()
        ->and(file_exists(base_path('phpstan.neon')))->toBeTrue()
        ->and(file_exists(base_path('rector.php')))->toBeTrue();
});

it('installs essentials without configuration', function () {
    $this->artisan('essentials:install -y')
        ->expectsQuestion('What would you like to install and configure?', [
            'laravel/pint',
            'larastan/larastan',
            'driftingly/rector-laravel',
        ])
        ->expectsConfirmation('Do you want to add basic configuration methods to the AppServiceProvider?', 'no')
        ->assertExitCode(0);

    expect(file_exists(base_path('pint.json')))->toBeTrue()
        ->and(file_exists(base_path('phpstan.neon')))->toBeTrue()
        ->and(file_exists(base_path('rector.php')))->toBeTrue();
});

it('installs essentials with selected only `laravel/pint` correctly', function () {
    $this->artisan('essentials:install -y')
        ->expectsQuestion('What would you like to install and configure?', [
            'laravel/pint',
        ])
        ->expectsConfirmation('Do you want to add basic configuration methods to the AppServiceProvider?', 'no')
        ->assertExitCode(0);

    expect(file_exists(base_path('pint.json')))->toBeTrue()
        ->and(file_exists(base_path('phpstan.neon')))->toBeFalse()
        ->and(file_exists(base_path('rector.php')))->toBeFalse();
});

it('instlls essentials with selected only `larastan/larastan` correctly', function () {
    $this->artisan('essentials:install -y')
        ->expectsQuestion('What would you like to install and configure?', [
            'larastan/larastan',
        ])
        ->expectsConfirmation('Do you want to add basic configuration methods to the AppServiceProvider?', 'no')
        ->assertExitCode(0);

    expect(file_exists(base_path('pint.json')))->toBeFalse()
        ->and(file_exists(base_path('phpstan.neon')))->toBeTrue()
        ->and(file_exists(base_path('rector.php')))->toBeFalse();
});

it('installs essentials with selected only `driftingly/rector-laravel` correctly', function () {
    $this->artisan('essentials:install -y')
        ->expectsQuestion('What would you like to install and configure?', [
            'driftingly/rector-laravel',
        ])
        ->expectsConfirmation('Do you want to add basic configuration methods to the AppServiceProvider?', 'no')
        ->assertExitCode(0);

    expect(file_exists(base_path('pint.json')))->toBeFalse()
        ->and(file_exists(base_path('phpstan.neon')))->toBeFalse()
        ->and(file_exists(base_path('rector.php')))->toBeTrue();
});

it('fails when `AppServiceProvider.php` is not found', function () {
    unlink(app_path('Providers/AppServiceProvider.php'));

    $this->artisan('essentials:install -y')
        ->expectsQuestion('What would you like to install and configure?', [
            'laravel/pint',
            'larastan/larastan',
            'driftingly/rector-laravel',
        ])
        ->expectsConfirmation('Do you want to add basic configuration methods to the AppServiceProvider?', 'yes')
        ->expectsOutputToContain('AppServiceProvider.php not found. Please create it first.')
        ->assertExitCode(1);

    expect(file_exists(base_path('pint.json')))->toBeTrue()
        ->and(file_exists(base_path('phpstan.neon')))->toBeTrue()
        ->and(file_exists(base_path('rector.php')))->toBeTrue();
});
