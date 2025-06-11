<?php

use Patressz\Essentials\Enums\ConfigureOption;

beforeEach(function () {
    $appServiceProviderPath = app_path('Providers/AppServiceProvider.php');
    $fixtureStubPath = __DIR__.'/Fixtures/AppServiceProvider.stub';

    copy($fixtureStubPath, $appServiceProviderPath);
});

it('installs essentials and configures the application correctly', function () {
    $configurableOptions = collect(ConfigureOption::cases())
        ->mapWithKeys(fn (ConfigureOption $option) => [$option->value => $option->getLabel()])
        ->toArray();

    $this->artisan('essentials:install -y')
        ->expectsQuestion('What would you like to install and configure?', [
            'laravel/pint',
            'larastan/larastan',
            'driftingly/rector-laravel',
        ])
        ->expectsConfirmation('Do you want to add basic configuration methods to the AppServiceProvider?', 'yes')
        ->expectsQuestion('Which configuration methods would you like to add?', $configurableOptions)
        ->assertExitCode(0);

    expect(file_exists(base_path('pint.json')))->toBeTrue()
        ->and(file_exists(base_path('phpstan.neon')))->toBeTrue()
        ->and(file_exists(base_path('rector.php')))->toBeTrue();

    $expectedContent = file_get_contents(app_path('Providers/AppServiceProvider.php'));

    expect($expectedContent)
        ->toMatchSnapshot();
});
