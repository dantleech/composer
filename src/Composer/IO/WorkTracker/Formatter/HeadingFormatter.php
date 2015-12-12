<?php


namespace Composer\IO\WorkTracker\Formatter;
use Composer\IO\WorkTracker\AbstractWorkTracker;
use Composer\IO\WorkTracker\FormatterInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * A formatter which prints a message at the beginning of each main task.
 * Through doing so look exactly like Composer without the work tracker.
 */
class HeadingFormatter implements FormatterInterface {

    /**
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    protected $output;

    /**
     * Only display the message if it starts with `<info>`
     *
     * @var bool
     */
    protected $onlyInfo;

    public function __construct(OutputInterface $output, $onlyInfo = false)
    {
        $this->output = $output;
        $this->onlyInfo = $onlyInfo;
    }

    /**
     * Called when work tracker is created
     *
     * @param AbstractWorkTracker $workTracker
     */
    public function create(AbstractWorkTracker $workTracker)
    {
        if($workTracker->getDepth() == 1) {
            if($this->onlyInfo && !$this->startsWith($workTracker->getTitle(), '<info>')) {
                return;
            }
            $this->output->writeln($workTracker->getTitle());
        } else if($this->startsWith($workTracker->getTitle(), '> ')) {
            $this->output->writeln($workTracker->getTitle());
        }
    }

    /**
     * Called when work tracker is completed
     *
     * @param AbstractWorkTracker $workTracker
     */
    public function complete(AbstractWorkTracker $workTracker)
    {
    }

    /**
     * Called when the work tracker is "pinged" (notified of
     * some progress).
     *
     * @param AbstractWorkTracker $workTracker
     */
    public function ping(AbstractWorkTracker $workTracker)
    {
    }

    private function startsWith($haystack, $needle)
    {
        $length = strlen($needle);
        return (substr($haystack, 0, $length) === $needle);
    }
}