<?php

namespace QafooLabs\Refactoring\Domain\Services;

use QafooLabs\Refactoring\Domain\Model\LineRange;
use QafooLabs\Refactoring\Domain\Model\File;
use QafooLabs\Refactoring\Domain\Model\DefinedVariables;

interface ParserScanner
{
    /**
     * Scan a line range within a file.
     *
     * @return DefinedVariables
     */
    public function scan(File $file, LineRange $range);
}
