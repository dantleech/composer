<?php


namespace Composer\IO\WorkTracker\Formatter;
use Composer\IO\WorkTracker\AbstractWorkTracker;
use Composer\IO\WorkTracker\FormatterInterface;

/**
 * A formatter which does nothing.
 * Primary used for testing.
 *
 * @package Composer\IO\WorkTracker\Formatter
 */
class EmptyFormatter implements FormatterInterface {

    /**
     * Called when work tracker is created
     *
     * @param AbstractWorkTracker
     */
    public function create(AbstractWorkTracker $workTracker) {
    }

    /**
     * Called when work tracker is completed
     *
     * @param AbstractWorkTracker
     */
    public function complete(AbstractWorkTracker $workTracker) {
    }

    /**
     * Called when the work tracker is "pinged" (notified of
     * some progress).
     *
     * @param AbstractWorkTracker $workTracker
     */
    public function ping(AbstractWorkTracker $workTracker) {
    }
}