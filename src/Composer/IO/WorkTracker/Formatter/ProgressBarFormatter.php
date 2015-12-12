<?php

namespace Composer\IO\WorkTracker\Formatter;

use Composer\IO\WorkTracker\FormatterInterface;
use Composer\IO\WorkTracker\AbstractWorkTracker;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Helper\ProgressHelper;
use Symfony\Component\Console\Output\OutputInterface;
use Composer\IO\WorkTracker\BoundWorkTracker;

/**
 * A work tracker formatter which shows a progress bar for each major task.
 */
class ProgressBarFormatter extends DebouncedFormatter implements FormatterInterface, InteractsWithConsoleOutput
{

    protected $precision = 1000;

    protected $output;

    protected $columns;

    public static $noMessage = '';

    /**
     * True if the formatter should be displaying a progress bar at the given time.
     * This doesn't necessarily mean the progress bar is visible though, as sometimes
     * it needs to be momentarily erased then recreated to make way for other output.
     * If the task is unbounded, then a progress bar won't be shown.
     *
     * @var bool
     */
    protected $hasTaskToDisplay = false;

    protected $outputLog;

    /**
     * @param OutputInterface $output
     * @param bool            $outputLog
     */
    public function __construct(OutputInterface $output, $outputLog = true)
    {
        parent::__construct();

        $this->output = $output;
        $this->outputLog = $outputLog;
        $this->hasTaskToDisplay = false;
        ProgressBar::setPlaceholderFormatterDefinition('dpercent', function(ProgressBar $bar) {
            return round($bar->getProgressPercent() * 100, 1);
        });
        $this->progressBar = new ProgressBar($output);
        $this->progressBar->setBarCharacter('<fg=green>█</>');
        $this->progressBar->setProgressCharacter('<fg=green>█</>');
        $this->progressBar->setEmptyBarCharacter('░');

        $this->columns = exec('tput cols', $out, $return);
        if($return !== 0) {
            $this->columns = 40;
        }
        $this->progressBar->setFormat(($outputLog ? str_repeat("─", $this->columns) . "\n" : '') . "[%bar%]%percent:3s%%\n<fg=cyan>%mainMessage%</>: %message%");
    }

    /**
     * {@inheritdoc}
     */
    public function create(AbstractWorkTracker $workTracker)
    {
        if($workTracker->getDepth() == 1) {
            $this->progressBar->setMessage($workTracker->getTitle(), 'mainMessage');
            $this->progressBar->setMessage(static::$noMessage);

            if($workTracker instanceof BoundWorkTracker) {
                $this->progressBar->start($this->precision);
            } else {
                return;
            }

            $this->progressBar->displayFromStart();
            $this->hasTaskToDisplay = true;
        } else if($this->hasTaskToDisplay === true) {
            $this->progressBar->setMessage($workTracker->getTitle());
            $this->progressBar->display();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function complete(AbstractWorkTracker $workTracker)
    {
        if($this->hasTaskToDisplay === false) {
            return false;
        }
        if($workTracker->getDepth() === 1) {
            $this->progressBar->clearToStart();
            $this->hasTaskToDisplay = false;
        } elseif($workTracker->getDepth() === 2) {
            $this->progressBar->setMessage(static::$noMessage);
            $this->progressBar->display();
        } else {
            $this->progressBar->setMessage($workTracker->getParent()->getTitle());
            $this->progressBar->display();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function ping(AbstractWorkTracker $workTracker)
    {
        if($this->hasTaskToDisplay === false || !$this->shouldDisplayAgain()) {
            return;
        }

        $this->setProgress($workTracker->estimateProgress());
    }

    protected function setProgress($decimal) {
        $this->progressBar->setProgress(round($decimal * $this->precision));
    }

    /**
     * Signals that a line is about to be written to the output, therefore we should hide the progress bar to prevent any visual glitches.
     */
    public function beforeWrite()
    {
        if($this->outputLog) {
            // clears the progress bar
            if($this->progressBar->isVisible()) {
                $this->progressBar->clearToStart();
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * Displays the progress bar again, after output has been written to.
     */
    public function afterWrite()
    {
        if($this->outputLog) {
            // only redraw the progress bar if we need to
            if($this->hasTaskToDisplay && !$this->progressBar->isVisible()) {
                $this->progressBar->displayFromStart();
            }
        }
    }

}