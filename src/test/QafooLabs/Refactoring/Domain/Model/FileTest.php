<?php

namespace QafooLabs\Refactoring\Domain\Model;

class FileTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->file = new File('relative/path/to', <<<'PHP'
<?php
class Foo
{
    public function main()
    {
        echo 'Hello World!!';
    }
}
PHP
        );
    }

    public function testGetRelativePath()
    {
        $this->assertEquals('relative/path/to', $this->file->getRelativePath());
    }

    public function testGetCode()
    {
        $this->assertEquals(<<<'PHP'
<?php
class Foo
{
    public function main()
    {
        echo 'Hello World!!';
    }
}
PHP
        , $this->file->getCode());
    }

    public function testGetLineRange()
    {
        $range = $this->file->getLineRange();
        $this->assertNotNull($range);
        $this->assertTrue($range instanceof LineRange);
        $this->assertEquals(1, $range->getStart());
        $this->assertEquals(8, $range->getEnd());
    }
}
