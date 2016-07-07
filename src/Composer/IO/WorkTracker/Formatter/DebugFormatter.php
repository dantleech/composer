<?php

namespace Composer\IO\WorkTracker\Formatter;

use Composer\IO\WorkTracker\FormatterInterface;
use Composer\IO\WorkTracker\AbstractWorkTracker;
use Composer\IO\WorkTracker\BoundWorkTracker;
use Symfony\Component\Console\Output\OutputInterface;

class DebugFormatter implements FormatterInterface
{
    protected $output;
    protected $lastOutputTime;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;

    }

    public function create(AbstractWorkTracker $workTracker)
    {
        //if($workTracker->getDepth() == 1) {
            $this->formatMessage($workTracker, 'BEGIN ', $workTracker->getTitle());
        //}
    }

    public function complete(AbstractWorkTracker $workTracker)
    {
        $this->formatMessage($workTracker, 'DONE  ', $workTracker->getTitle());
    }

    public function ping(AbstractWorkTracker $workTracker)
    {
        if($workTracker instanceof BoundWorkTracker) {
            $this->formatMessage($workTracker, 'PING  ', sprintf(
                '[#%03d/%03d] [%ss]',
                $workTracker->getPingCount(),
                $workTracker->getMax(),
                $workTracker->getElapsedPingTime()
            ));
        } else {
            $this->formatMessage($workTracker, 'PING  ', sprintf(
                '[#%06d] [%ss]',
                $workTracker->getPingCount(),
                $workTracker->getElapsedPingTime()
            ));
        }

    }

    private function formatMessage(AbstractWorkTracker $workTracker, $subject, $message)
    {
        $this->output->writeln(sprintf('%s%s: %s', str_repeat('  ', $workTracker->getDepth() - 1), $subject, $message));
    }
}
