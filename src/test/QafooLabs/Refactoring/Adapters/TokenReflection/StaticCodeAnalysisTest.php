<?php

namespace QafooLabs\Refactoring\Adapters\TokenReflection;

use QafooLabs\Refactoring\Domain\Model\LineRange;
use QafooLabs\Refactoring\Domain\Model\File;

class StaticCodeAnalysisTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->file = new File('relative/path/to', <<<'PHP'
<?php
class Foo
{
    public static $static_foo;
    public $foo;
    protected $bar;
    private $baz;

    public static function main()
    {
        echo 'Hello World!!';
    }

    public function getFoo()
    {
        return $this->foo;
    }

    protected function getBar()
    {
        return $this->bar;
    }

    private function getBaz()
    {
        return $this->baz;
    }

}
PHP
        );
        $this->analysis = new StaticCodeAnalysis();
    }

    public function testIsMethodStatic()
    {
        $this->assertTrue($this->analysis->isMethodStatic(
            $this->file,
            LineRange::fromSingleLine(11)
        ));
        $this->assertFalse($this->analysis->isMethodStatic(
            $this->file,
            LineRange::fromSingleLine(16)
        ));
        $this->assertFalse($this->analysis->isMethodStatic(
            $this->file,
            LineRange::fromSingleLine(21)
        ));
        $this->assertFalse($this->analysis->isMethodStatic(
            $this->file,
            LineRange::fromSingleLine(26)
        ));

        /**
         * at the outside of methods
         */
         $this->assertFalse($this->analysis->isMethodStatic(
            $this->file,
            LineRange::fromSingleLine(4)
         ));

        /**
         * at the start of curly brace "{"
         */
         $this->assertTrue($this->analysis->isMethodStatic(
            $this->file,
            LineRange::fromSingleLine(10)
         ));

        /**
         * at the end of curly brace "}"
         */
         $this->assertFalse($this->analysis->isMethodStatic(
            $this->file,
            LineRange::fromSingleLine(12)
         ));
    }

    public function testGetMethodEndLine()
    {
        $this->assertEquals(12, $this->analysis->getMethodEndLine(
            $this->file,
            LineRange::fromSingleLine(11)
        ));
        $this->assertEquals(17, $this->analysis->getMethodEndLine(
            $this->file,
            LineRange::fromSingleLine(16)
        ));
        $this->assertEquals(22, $this->analysis->getMethodEndLine(
            $this->file,
            LineRange::fromSingleLine(21)
        ));
        $this->assertEquals(27, $this->analysis->getMethodEndLine(
            $this->file,
            LineRange::fromSingleLine(26)
        ));
    }

    public function testGetMethodEndLineAtOutside_ThrowException()
    {
        $this->setExpectedException('\InvalidArgumentException', 'Could not find method end line.');
        $this->analysis->getMethodEndLine(
            $this->file,
            LineRange::fromSingleLine(4)
        );
    }

    public function testGetMethodEndLineAtEndOfBrace_ThrowException()
    {
        $this->setExpectedException('\InvalidArgumentException', 'Could not find method end line.');
        $this->analysis->getMethodEndLine(
            $this->file,
            LineRange::fromSingleLine(12)
        );
    }

    public function testGetMethodEndLineAtStartOfBrace()
    {
        $this->assertEquals(12, $this->analysis->getMethodEndLine(
            $this->file,
            LineRange::fromSingleLine(10)
        ));
    }

    public function testGetMethodStartLine()
    {
        $this->assertEquals(9, $this->analysis->getMethodStartLine(
            $this->file,
            LineRange::fromSingleLine(11)
        ));
        $this->assertEquals(14, $this->analysis->getMethodStartLine(
            $this->file,
            LineRange::fromSingleLine(16)
        ));
        $this->assertEquals(19, $this->analysis->getMethodStartLine(
            $this->file,
            LineRange::fromSingleLine(21)
        ));
        $this->assertEquals(24, $this->analysis->getMethodStartLine(
            $this->file,
            LineRange::fromSingleLine(26)
        ));
    }

    public function testGetMethodStartLineAtOutside_ThrowException()
    {
        $this->setExpectedException('\InvalidArgumentException', 'Could not find method start line.');
        $this->analysis->getMethodStartLine(
            $this->file,
            LineRange::fromSingleLine(4)
        );
    }

    public function testGetMethodStartLineAtEndOfBrace_ThrowException()
    {
        $this->setExpectedException('\InvalidArgumentException', 'Could not find method start line.');
        $this->analysis->getMethodStartLine(
            $this->file,
            LineRange::fromSingleLine(12)
        );
    }

    public function testGetMethodStartLineAtStartOfBrace()
    {
        $this->assertEquals(9, $this->analysis->getMethodStartLine(
            $this->file,
            LineRange::fromSingleLine(10)
        ));
    }

    public function testGetLineOfLastPropertyDefinedInScope()
    {
        $this->assertEquals(7, $this->analysis->getLineOfLastPropertyDefinedInScope(
            $this->file,
            11
        ));
        $this->assertEquals(7, $this->analysis->getLineOfLastPropertyDefinedInScope(
            $this->file,
            16
        ));
        $this->assertEquals(7, $this->analysis->getLineOfLastPropertyDefinedInScope(
            $this->file,
            21
        ));
        $this->assertEquals(7, $this->analysis->getLineOfLastPropertyDefinedInScope(
            $this->file,
            26
        ));
    }

    public function testGetLineOfLastPropertyDefinedInScopeAtOutside_ThrowException()
    {
        $this->setExpectedException('\InvalidArgumentException', 'Could not find method start line.');
        $this->analysis->getLineOfLastPropertyDefinedInScope(
            $this->file,
            4
        );
    }

    public function testGetLineOfLastPropertyDefinedInScopeAtEndOfBrace_ThrowException()
    {
        $this->setExpectedException('\InvalidArgumentException', 'Could not find method start line.');
        $this->analysis->getLineOfLastPropertyDefinedInScope(
            $this->file,
            12
        );
    }

    public function testGetLineOfLastPropertyDefinedInScopeAtStartOfBrace()
    {
        $this->assertEquals(7, $this->analysis->getLineOfLastPropertyDefinedInScope(
            $this->file,
            10
        ));
    }

    public function testIsInsideMethod()
    {
        $this->assertTrue($this->analysis->isInsideMethod(
            $this->file,
            LineRange::fromSingleLine(11)
        ));
        $this->assertTrue($this->analysis->isInsideMethod(
            $this->file,
            LineRange::fromSingleLine(16)
        ));
        $this->assertTrue($this->analysis->isInsideMethod(
            $this->file,
            LineRange::fromSingleLine(21)
        ));
        $this->assertTrue($this->analysis->isInsideMethod(
            $this->file,
            LineRange::fromSingleLine(26)
        ));

        /**
         * at the outside of methods
         */
         $this->assertFalse($this->analysis->isInsideMethod(
            $this->file,
            LineRange::fromSingleLine(4)
         ));

        /**
         * at the start of curly brace "{"
         */
         $this->assertTrue($this->analysis->isInsideMethod(
            $this->file,
            LineRange::fromSingleLine(10)
         ));

        /**
         * at the end of curly brace "}"
         */
         $this->assertFalse($this->analysis->isInsideMethod(
            $this->file,
            LineRange::fromSingleLine(12)
         ));
    }

    public function testIsFieldStatic()
    {
        $this->assertTrue($this->analysis->isFieldStatic(
            $this->file,
            LineRange::fromSingleLine(4)
        ));
        $this->assertFalse($this->analysis->isFieldStatic(
            $this->file,
            LineRange::fromSingleLine(5)
        ));
        $this->assertFalse($this->analysis->isFieldStatic(
            $this->file,
            LineRange::fromSingleLine(6)
        ));
        $this->assertFalse($this->analysis->isFieldStatic(
            $this->file,
            LineRange::fromSingleLine(7)
        ));
    }

    public function testGetLineOfLastMethodEndLine()
    {
        $this->assertEquals(27, $this->analysis->getLineOfLastMethodEndLine(
            $this->file,
            LineRange::fromSingleLine(5)
        ));

        $this->file = new File('relative/path/to', <<<'PHP'
<?php
class Foo
{
    public static $static_foo;
    public $foo;
    protected $bar;
    private $baz;
}
PHP
        );
        $this->assertEquals(7, $this->analysis->getLineOfLastMethodEndLine(
            $this->file,
            LineRange::fromSingleLine(5)
        ));

        $this->file = new File('relative/path/to', <<<'PHP'
<?php
class Foo
{
}
PHP
        );
        $this->assertEquals(3, $this->analysis->getLineOfLastMethodEndLine(
            $this->file,
            LineRange::fromSingleLine(4)
        ));
    }

    public function testGetLineOfLastPropertyDefined()
    {
        $this->assertEquals(7, $this->analysis->getLineOfLastPropertyDefined(
            $this->file,
            LineRange::fromSingleLine(4)
        ));

        $this->file = new File('relative/path/to', <<<'PHP'
<?php
class Foo
{
    public static function main()
    {
        echo 'Hello World!!';
    }
}
PHP
        );
        $this->assertEquals(3, $this->analysis->getLineOfLastPropertyDefined(
            $this->file,
            LineRange::fromSingleLine(4)
        ));

        $this->file = new File('relative/path/to', <<<'PHP'
<?php
class Foo
{
}
PHP
        );
        $this->assertEquals(3, $this->analysis->getLineOfLastPropertyDefined(
            $this->file,
            LineRange::fromSingleLine(4)
        ));
    }

    public function testGetClassEndLine()
    {
        $this->assertEquals(29, $this->analysis->getClassEndLine(
            $this->file,
            LineRange::fromSingleLine(4)
        ));

        $this->file = new File('relative/path/to', <<<'PHP'
<?php
class Foo
{
    public static function main()
    {
        echo 'Hello World!!';
    }
}
class Bar
{
    public static function main()
    {
        echo 'Hello World!!';
    }
}
PHP
        );
        $this->assertEquals(8, $this->analysis->getClassEndLine(
            $this->file,
            LineRange::fromSingleLine(4)
        ));
        $this->assertEquals(15, $this->analysis->getClassEndLine(
            $this->file,
            LineRange::fromSingleLine(9)
        ));

        $this->file = new File('relative/path/to', <<<'PHP'
<?php
class Foo
{
}
PHP
        );
        $this->assertEquals(4, $this->analysis->getClassEndLine(
            $this->file,
            LineRange::fromSingleLine(4)
        ));
    }

    public function testGetClassEndLine_ThrowException()
    {
        $this->setExpectedException('\InvalidArgumentException', 'Could not find class end line.');
        $this->file = new File('relative/path/to', <<<'PHP'
<?php
class Foo
{
}
PHP
        );
        $this->assertEquals(29, $this->analysis->getClassEndLine(
            $this->file,
            LineRange::fromSingleLine(5)
        ));
    }
}
