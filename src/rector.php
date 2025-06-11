<?php

namespace Patressz\Essentials;

use Patressz\Essentials\Rector\AppServiceProviderConfigurationRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withRules([
        AppServiceProviderConfigurationRector::class,
    ]);
