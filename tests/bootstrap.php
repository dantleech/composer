<?php

/*
 * This file is part of Composer.
 *
 * (c) Nils Adermann <naderman@naderman.de>
 *     Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Composer\IO\WorkTracker\ContextWorkTracker;
use Composer\IO\WorkTracker\Formatter\EmptyFormatter;
use Composer\IO\WorkTracker\UnboundWorkTracker;

error_reporting(E_ALL);

if (function_exists('date_default_timezone_set') && function_exists('date_default_timezone_get')) {
    date_default_timezone_set(@date_default_timezone_get());
}

/**
 * Creates a stub work tracker used for testing
 *
 * @return \Composer\IO\WorkTracker\ContextWorkTracker
 */
function createWorkTrackerForTesting() {
    $masterWorkTracker = new UnboundWorkTracker('Composer Install', new EmptyFormatter());
    return new ContextWorkTracker($masterWorkTracker);
}

require __DIR__.'/../src/bootstrap.php';
require __DIR__.'/Composer/TestCase.php';
