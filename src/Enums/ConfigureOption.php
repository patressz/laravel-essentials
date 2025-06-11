<?php

namespace Patressz\Essentials\Enums;

enum ConfigureOption: string
{
    case CONFIGURE_MODELS_STRICTNESS = 'configureModelsStrictness';
    case CONFIGURE_MODELS_AUTOMATICALLY_EAGER_LOADING_RELATIONSHIPS = 'configureModelsAutomaticallyEagerLoadingRelationships';
    case CONFIGURE_MODELS_UNGUARDED = 'configureModelsUnguarded';
    case CONFIGURE_DATES = 'configureDates';
    case CONFIGURE_COMMANDS = 'configureCommands';
    case CONFIGURE_HTTP_SCHEME = 'configureHttpScheme';
    case CONFIGURE_ASSET_PREFETCHING = 'configureAssetPrefetching';

    /**
     * Get the label for the configuration option.
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::CONFIGURE_MODELS_STRICTNESS => 'Configure models to be strict',
            self::CONFIGURE_MODELS_AUTOMATICALLY_EAGER_LOADING_RELATIONSHIPS => 'Configure models to automatically eager load relationships',
            self::CONFIGURE_MODELS_UNGUARDED => 'Configure models to be unguarded',
            self::CONFIGURE_DATES => 'Configure dates (use CarbonImmutable)',
            self::CONFIGURE_COMMANDS => 'Disable destructive commands in production like <fg=blue;options=bold;>php artisan migrate:fresh</>',
            self::CONFIGURE_HTTP_SCHEME => 'Configure HTTP scheme for production',
            self::CONFIGURE_ASSET_PREFETCHING => 'Configure asset prefetching',
        };
    }

    /**
     * Get the method name for the configuration option.
     */
    public function getConfigurationMethod(): string
    {
        return match ($this) {
            self::CONFIGURE_MODELS_STRICTNESS => 'configureModels',
            self::CONFIGURE_MODELS_AUTOMATICALLY_EAGER_LOADING_RELATIONSHIPS => 'configureModels',
            self::CONFIGURE_MODELS_UNGUARDED => 'configureModels',
            self::CONFIGURE_DATES => 'configureDates',
            self::CONFIGURE_COMMANDS => 'configureCommands',
            self::CONFIGURE_HTTP_SCHEME => 'configureHttpScheme',
            self::CONFIGURE_ASSET_PREFETCHING => 'configureAssetPrefetching',
        };
    }
}
