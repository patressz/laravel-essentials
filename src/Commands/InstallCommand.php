<?php

namespace Patressz\Essentials\Commands;

use Illuminate\Console\Command;
use Patressz\Essentials\Enums\ConfigureOption;
use Symfony\Component\Process\Process;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\multiselect;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'essentials:install 
                            {--y|yes : Skip confirmation prompts and overwrite existing configuration files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the laravel starter kit';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->installOrConfigurePackages();
        $this->configureServiceProvider();

        $this->components->info('Installation and configuration completed successfully!');
    }

    /**
     * Installs or configures the selected packages.
     */
    private function installOrConfigurePackages(): void
    {
        $packages = multiselect('What would you like to install and configure?', [
            'laravel/pint' => 'Laravel Pint',
            'larastan/larastan' => 'PHPStan (larastan)',
            'driftingly/rector-laravel' => 'Rector',
        ], required: true);

        $this->requireComposerPackages($packages, true);

        foreach ($packages as $package) {
            $packageConfig = match ($package) {
                'laravel/pint' => 'pint.json',
                'larastan/larastan' => 'phpstan.neon',
                'driftingly/rector-laravel' => 'rector.php',
            };

            if (file_exists(base_path($packageConfig)) && ! $this->option('yes')) {
                if (! confirm("{$package} is already installed. Do you want to overwrite the existing {$packageConfig} file?")) {
                    continue;
                }
            }

            $stubPath = sprintf(__DIR__.'/../../stubs/%s.stub', str()->before($packageConfig, '.'));
            copy($stubPath, base_path($packageConfig));
        }
    }

    /**
     * Configures the AppServiceProvider with basic configuration methods.
     */
    private function configureServiceProvider(): void
    {
        $ask = confirm('Do you want to add basic configuration methods to the AppServiceProvider?', true);

        if (! $ask) {
            return;
        }

        $serviceProviderPath = app_path('Providers/AppServiceProvider.php');

        if (! file_exists($serviceProviderPath)) {
            $this->components->error('AppServiceProvider.php not found. Please create it first.');

            return;
        }

        $configurableOptions = collect(ConfigureOption::cases())
            ->mapWithKeys(fn (ConfigureOption $option) => [$option->value => $option->getLabel()])
            ->all();

        $selectedOptions = multiselect(
            'Which configuration methods would you like to add?',
            $configurableOptions,
            scroll: 10,
            required: true
        );

        $selectedOptionsString = implode(',', array_values($selectedOptions));

        $rectorConfigPath = __DIR__.'/../rector.php';

        Process::fromShellCommandline(
            "SELECTED_OPTIONS=\"{$selectedOptionsString}\" vendor/bin/rector process {$serviceProviderPath} --config {$rectorConfigPath} --clear-cache --debug"
        )->run(function (string $type, string $output) {
            $this->output->write($output);
        });

        if (! file_exists('vendor/bin/pint')) {
            return;
        }

        Process::fromShellCommandline(
            "vendor/bin/pint {$serviceProviderPath}"
        )->run();
    }

    /**
     * Installs the given Composer Packages into the application.
     * Taken from https://github.com/laravel/breeze/blob/1.x/src/Console/InstallCommand.php
     *
     * @param  list<string>  $packages
     */
    protected function requireComposerPackages(array $packages, bool $asDev = false): bool
    {
        $command = [
            ...['composer', 'require'],
            ...$packages,
            ...$asDev ? ['--dev'] : [],
        ];

        return (new Process($command, base_path(), ['COMPOSER_MEMORY_LIMIT' => '-1']))
            ->setTimeout(null)
            ->run(function (string $type, string $output) {
                $this->output->write($output);
            }) === 0;
    }
}
