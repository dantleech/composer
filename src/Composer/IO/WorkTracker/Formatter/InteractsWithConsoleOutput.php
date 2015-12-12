<?php

namespace Composer\IO\WorkTracker\Formatter;


interface InteractsWithConsoleOutput {


    /**
     * Called before text is written to the output.
     *
     * @return bool  true if the text should still be written
     */
    public function beforeWrite();

    /**
     * Called after text is written to the output.
     */
    public function afterWrite();

}