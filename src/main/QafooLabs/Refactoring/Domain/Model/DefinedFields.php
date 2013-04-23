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


namespace QafooLabs\Refactoring\Domain\Model;

use Closure;

/**
 * Defined fields that are used or assigned.
 */
class DefinedFields implements DefinedElements
{
    /**
     * Name of fields that are assigned.
     *
     * @var array
     */
    protected $fields;

    public function __construct(array $fields = array())
    {
        $this->fields = $fields;
    }

    /**
     * Does list contain the given field?
     *
     * @return bool
     */
    public function contains(Element $field)
    {
        return isset($this->fields[$field->getName()]);
    }
}
