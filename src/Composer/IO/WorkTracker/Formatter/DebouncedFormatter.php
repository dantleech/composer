<?php


namespace Composer\IO\WorkTracker\Formatter;

/**
 * Makes the formatter only update after a given time interval,
 * to improve performance and reduce unecessary output.
 *
 * @package Composer\IO\WorkTracker\Formatter
 */
abstract class DebouncedFormatter {

    protected $lastOutputTime;
    protected $updateAfter = 0.1;

    public function __construct() {
        $this->lastOutputTime = microtime(true);
    }

    protected function shouldDisplayAgain()
    {
        $currentTime = microtime(true);
        if($this->lastOutputTime + $this->updateAfter < $currentTime) {
            $this->lastOutputTime = $currentTime;
            return true;
        } else {
            return false;
        }
    }

}