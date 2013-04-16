<?php
/**
 * Qafoo PHP Refactoring Browser
 *
 * LICENSE
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.txt.
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to kontakt@beberlei.de so I can send you a copy immediately.
 */

namespace QafooLabs\Patches;

class RemoveOperation implements Operation
{
    private $originalLine;

    public function __construct($originalLine)
    {
        $this->originalLine = $originalLine;
    }

    public function perform(Hunk $hunk)
    {
        return $hunk->removeLine($this->originalLine);
    }

    public function merge(array $mergeLines)
    {
        throw \BadMethodCallException('not implemented yet');
    }
}
