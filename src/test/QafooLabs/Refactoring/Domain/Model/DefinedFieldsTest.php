<?php

namespace QafooLabs\Refactoring\Domain\Model;

class DefinedFieldsTest extends \PHPUnit_Framework_TestCase
{
    public function testContains()
    {
        $defined_fields = new DefinedFields(array('foo' => 1, 'bar' => 2));

        $this->assertTrue($defined_fields->contains(new Field('foo')));
        $this->assertTrue($defined_fields->contains(new Field('bar')));
        $this->assertFalse($defined_fields->contains(new Field('baz')));
    }
}
