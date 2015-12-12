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

namespace Composer\Command;

use Composer\Composer;
use Composer\Console\Application;
use Composer\IO\IOInterface;
use Composer\IO\NullIO;
use Composer\IO\WorkTracker\Formatter\DebugFormatter;
use Composer\IO\WorkTracker\Formatter\EmptyFormatter;
use Composer\IO\WorkTracker\Formatter\GlobalProgressBarFormatter;
use Composer\IO\WorkTracker\Formatter\MultiProgressFormatter;
use Composer\IO\WorkTracker\Formatter\ProgressBarFormatter;
use InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command as BaseCommand;

/**
 * Base class for Composer commands
 *
 * @author Ryan Weaver <ryan@knplabs.com>
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class Command extends BaseCommand
{
    /**
     * @var Composer
     */
    private $composer;

    /**
     * @var IOInterface
     */
    private $io;

    /**
     * @param  bool              $required
     * @param  bool              $disablePlugins
     * @throws \RuntimeException
     * @return Composer
     */
    public function getComposer($required = true, $disablePlugins = false)
    {
        if (null === $this->composer) {
            $application = $this->getApplication();
            if ($application instanceof Application) {
                /* @var $application    Application */
                $this->composer = $application->getComposer($required, $disablePlugins);
            } elseif ($required) {
                throw new \RuntimeException(
                    'Could not create a Composer\Composer instance, you must inject '.
                    'one if this command is not used with a Composer\Console\Application instance'
                );
            }
        }

        return $this->composer;
    }

    /**
     * @param Composer $composer
     */
    public function setComposer(Composer $composer)
    {
        $this->composer = $composer;
    }

    /**
     * Removes the cached composer instance
     */
    public function resetComposer()
    {
        $this->composer = null;
        $this->getApplication()->resetComposer();
    }

    /**
     * @return IOInterface
     */
    public function getIO()
    {
        if (null === $this->io) {
            $application = $this->getApplication();
            if ($application instanceof Application) {
                /* @var $application    Application */
                $this->io = $application->getIO();
            } else {
                $this->io = new NullIO();
            }
        }

        return $this->io;
    }

    /**
     * @param IOInterface $io
     */
    public function setIO(IOInterface $io)
    {
        $this->io = $io;
    }

    /**
     * {@inheritDoc}
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        if (true === $input->hasParameterOption(array('--no-ansi')) && $input->hasOption('no-progress')) {
            $input->setOption('no-progress', true);
        }

        parent::initialize($input, $output);
    }

    /**
     * Returns a work tracker formatter based upon the `--pretty` option.
     *
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return \Composer\IO\WorkTracker\FormatterInterface
     */
    public function getWorkTrackerFormatter(InputInterface $input, OutputInterface $output)
    {
        $pretty = $input->getOption('pretty');
        if ($pretty == 'debug') {
            return new DebugFormatter($output);
        } else if($pretty == 'multi') {
            return new MultiProgressFormatter($output);
        } else if($pretty == 'progress-bar') {
            return new ProgressBarFormatter($output);
        } else if($pretty == 'global-progress-bar') {
            // these are all just estimations and may be adjusted
            // there is quite possibly missing steps
            $heuristics = [
                'weights' => [
                    'Running scripts for `command`' => 1,
                    'Running scripts for `pre-update-cmd`' => 1,
                    'Running scripts for `pre-install-cmd`' => 1,
                    'Running scripts for `pre-dependencies-solving`' => 1,
                    'Running scripts for `post-dependencies-solving`' => 1,
                    'Removing unstable packages from the local repository (if they don\'t match the current stablitity settings)' => 1,
                    'Generating autoload files' => 5,
                    'Running scripts for `post-install-cmd`' => 5,
                    'Consolidating changes' => 5
                ]
            ];
            if($this->getName() == 'update') {
                $heuristics['numOperations'] = 10;
                $heuristics['weights']['Loading composer repositories with package information'] = 10;
                $heuristics['weights']['Processing dev packages'] = 5;
                $heuristics['weights']['Updating dependencies (including require-dev)'] = 5;
                $heuristics['weights']['Updating dependencies'] = 5;
                $heuristics['weights']['Solving dependencies'] = 20;
                $heuristics['weights']['Installing'] = 35; // 1 + 1 + 10 + 1 + 5 + 5 + 1 + 20 + 1 + 5 + x + 5 + 5 + 5 = 100, x = 35
            } else if($this->getName() == 'install') {
                $heuristics['weights']['Loading composer repositories with package information'] = 1;
                $heuristics['weights']['Installing dependencies (including require-dev) from lock file'] = 1;
                $heuristics['weights']['Installing dependencies from lock file'] = 1;
                $heuristics['weights']['Solving dependencies'] = 10;
                $heuristics['weights']['Processing dev packages'] = 2;
                $heuristics['weights']['Installing'] = 70; // 1 + 1+ 1 +1 + 2 + 1 + 10 + 1 +2
            }
            return new GlobalProgressBarFormatter($output, $heuristics);
        } else if($pretty == 'empty') {
            return new EmptyFormatter($output);
        } else {
            throw new InvalidArgumentException('Invalid option: `--pretty=' . $pretty . '`');
        }
    }

}
