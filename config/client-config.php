<?php

use Clearbooks\Labs\Client\Toggle\ToggleChecker;
use Clearbooks\Labs\Client\Toggle\UseCase\IsToggleActive;

return [
    IsToggleActive::class => DI\object( ToggleChecker::class )
];
