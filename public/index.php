<?php

use App\Kernel;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {
    if (!isset($context['APP_ENV']) || !is_string($context['APP_ENV'])) {
        throw new LogicException('APP_ENV not set or not a string.');
    }
    if (!isset($context['APP_DEBUG'])) {
        throw new LogicException('APP_DEBUG not set.');
    }

    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
