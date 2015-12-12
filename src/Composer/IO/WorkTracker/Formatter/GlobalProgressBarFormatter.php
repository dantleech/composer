<?php

namespace Composer\IO\WorkTracker\Formatter;

use Composer\IO\WorkTracker\FormatterInterface;
use Composer\IO\WorkTracker\AbstractWorkTracker;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Helper\ProgressHelper;
use Symfony\Component\Console\Output\OutputInterface;
use Composer\IO\WorkTracker\BoundWorkTracker;

/**
 * A work tracker formatter which shows one progress bar for the entire operation.
 * This progress bar probably isn't very accurate as some sections take longer than others, but
 * it provides a better overview of the entire task.
 */
class GlobalProgressBarFormatter extends ProgressBarFormatter
{

    protected $operationHeuristics;
    protected $previousFullProgress;

    /**
     * @param OutputInterface $output
     * @param array           $operationHeuristics a set of heuristics about how long
     *                                             the task is and which parts take the most time to complete
     */
    public function __construct(OutputInterface $output, $operationHeuristics)
    {
        parent::__construct($output);

        $this->operationHeuristics = $operationHeuristics;
        $this->hasTaskToDisplay = true;

        $this->previousFullProgress = 0;

        $this->progressBar->setBarWidth($this->columns > 100 ? 100 : $this->columns);
        $this->progressBar->setMessage('Starting', 'mainMessage');
        $this->progressBar->setMessage(static::$noMessage);
        $this->progressBar->start($this->precision);
        $this->progressBar->displayFromStart();
    }

    /**
     * {@inheritdoc}
     */
    public function create(AbstractWorkTracker $workTracker)
    {
        if($workTracker->getDepth() == 1) {
            $this->progressBar->setMessage($workTracker->getTitle(), 'mainMessage');
            $this->progressBar->setMessage(static::$noMessage);
        } else {
            $this->progressBar->setMessage($workTracker->getTitle());
        }

        $this->progressBar->display();
    }

    /**
     * {@inheritdoc}
     */
    public function complete(AbstractWorkTracker $workTracker)
    {
        if($workTracker->getDepth() == 0) {
            $this->progressBar->clearToStart();
            return;
        } else if($workTracker->getDepth() == 1) {
            $this->previousFullProgress += $this->getWeightForTitle($workTracker->getTitle());
            $this->setProgress($this->previousFullProgress);
        } else if($workTracker->getDepth() == 2) {
            $this->progressBar->setMessage(static::$noMessage);
        } else {
            $this->progressBar->setMessage($workTracker->getParent()->getTitle());
        }

        $this->progressBar->display();
    }

    /**
     * {@inheritdoc}
     */
    public function ping(AbstractWorkTracker $workTracker)
    {
        if(!$this->shouldDisplayAgain()) {
            return;
        }

        // this algorithm estimates a total progress by accumulating the status of several nested work trackers
        $progress = 0;
        $title = null;
        while ($parent = $workTracker->getParent()) {

            if ($workTracker instanceof BoundWorkTracker) {
                $progress /= $workTracker->getMax();
                $progress += $workTracker->getPingCount() / $workTracker->getMax();
            } else {
                $progress = 0;
            }

            if($workTracker->getDepth() == 1) {
                $title = $workTracker->getTitle();
            }

            $workTracker = $parent;
        }

        $progress = $this->previousFullProgress + ($progress * $this->getWeightForTitle($title));
        $this->setProgress($progress);
    }

    private function getWeightForTitle($title) {
        $title = trim(strip_tags($title));
        if(isset($this->operationHeuristics['weights'][$title])) {
            return $this->operationHeuristics['weights'][$title] / 100;
        } else {
            $this->output->writeln("Invalid step name " . $title);
            return 0.01; // 1%
        }
    }

}