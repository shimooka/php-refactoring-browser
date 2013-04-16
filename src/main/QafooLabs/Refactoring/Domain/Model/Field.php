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

/**
 * Represent a variable in the refactoring domain.
 */
class Field
{
    const IS_PUBLIC = 1;
    const IS_PRIVATE = 2;
    const IS_PROTECTED = 4;
    const IS_STATIC = 8;
    const IS_FINAL = 16;
    const IS_CONST = 32;

    private $name;
    private $flags;

    public function __construct($name, $flags = self::IS_PRIVATE)
    {
        if (preg_match('(([\s;\(\)]+))', $name)) {
            throw RefactoringException::illegalFieldName($name);
        }

        $this->name = ltrim($name, '$');
        $this->flags = $this->change($flags);
    }

    private function change($flags)
    {
        $visibility = (self::IS_PRIVATE | self::IS_PROTECTED | self::IS_PUBLIC);
        $allowedVisibilities = array(self::IS_PRIVATE, self::IS_PROTECTED, self::IS_PUBLIC);

        if (($flags & $visibility) === 0) {
            $flags = $flags | self::IS_PRIVATE;
        }

        if ( ! in_array(($flags & $visibility), $allowedVisibilities)) {
            throw new \InvalidArgumentException("Mix of visibilities is not allowed.");
        }

        return $flags;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return '$' . $this->name;
    }

    /**
     * Is this field private?
     *
     * @return bool
     */
    public function isPrivate()
    {
        return ($this->flags & self::IS_PRIVATE) > 0;
    }

    /**
     * Is this field protected?
     *
     * @return bool
     */
    public function isProtected()
    {
        return ($this->flags & self::IS_PROTECTED) > 0;
    }

    /**
     * Is this field public?
     *
     * @return bool
     */
    public function isPublic()
    {
        return ($this->flags & self::IS_PUBLIC) > 0;
    }

    /**
     * Is this field static?
     *
     * @return bool
     */
    public function isStatic()
    {
        return ($this->flags & self::IS_STATIC) > 0;
    }

    /**
     * Is this field final?
     *
     * @return bool
     */
    public function isFinal()
    {
        return ($this->flags & self::IS_FINAL) > 0;
    }

    /**
     * Is this field const?
     *
     * @return bool
     */
    public function isConst()
    {
        return ($this->flags & self::IS_CONST) > 0;
    }

    /**
     * @return string
     */
    public function getCamelName()
    {
        $parts = array();
        foreach (explode('_', $this->getName()) as $word) {
            $parts[] = ucfirst(strtolower($word));
        }

        return join($parts);
    }

    public function convertToGetter()
    {
        return new Variable('$this->get' . $this->getCamelName());
    }

    public function convertToSetter()
    {
        return new Variable('$this->set' . $this->getCamelName());
    }
}
