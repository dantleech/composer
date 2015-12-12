<?php

namespace Composer\IO\WorkTracker\Formatter;

use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class NotifyFormatterConsoleOutput extends NotifyFormatterOutput implements ConsoleOutputInterface {

    public function __construct(ConsoleOutputInterface $inner, InteractsWithConsoleOutput $notify) {
        parent::__construct($inner, $notify);
        $this->errorOutput = new NotifyFormatterOutput($inner->getErrorOutput(), $notify);
    }

    /**
     * Gets the OutputInterface for errors.
     *
     * @return OutputInterface
     */
    public function getErrorOutput() {
        return $this->errorOutput;
    }

    /**
     * Sets the OutputInterface used for errors.
     *
     * @param OutputInterface $error
     */
    public function setErrorOutput(OutputInterface $error) {
        $this->errorOutput = new NotifyFormatterOutput($error, $this->notify);
    }
}