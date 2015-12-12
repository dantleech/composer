<?php

namespace Composer\IO\WorkTracker\Formatter;


interface InteractsWithConsoleOutput {


    /**
     * Called before text is written to the output.
     */
    public function beforeWrite();

    /**
     * Called after text is written to the output.
     */
    public function afterWrite();

}