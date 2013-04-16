<?php

namespace QafooLabs\Refactoring\Domain\Model;

class FieldTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateInvalidField()
    {
        $this->setExpectedException('QafooLabs\Refactoring\Domain\Model\RefactoringException', 'The given field name "(); " is not valid in PHP.');

        new Field('(); ');
    }

    public function testGetNameOrToken()
    {
        $field = new Field('$var');

        $this->assertEquals('var', $field->getName());
        $this->assertEquals('$var', $field->getToken());
    }

    public function testGetCamelName()
    {
        $field = new Field('foo_bar_baz');
        $this->assertEquals('FooBarBaz', $field->getCamelName());
    }

    public function testModifiers()
    {
        $field = new Field('foo');

        $this->assertTrue($field->isPrivate());
        $this->assertFalse($field->isProtected());
        $this->assertFalse($field->isPublic());
        $this->assertFalse($field->isStatic());
        $this->assertFalse($field->isFinal());
        $this->assertFalse($field->isConst());

        $field = new Field('foo', Field::IS_PRIVATE);

        $this->assertTrue($field->isPrivate());
        $this->assertFalse($field->isProtected());
        $this->assertFalse($field->isPublic());
        $this->assertFalse($field->isStatic());
        $this->assertFalse($field->isFinal());
        $this->assertFalse($field->isConst());

        $field = new Field('foo', Field::IS_PROTECTED);

        $this->assertFalse($field->isPrivate());
        $this->assertTrue($field->isProtected());
        $this->assertFalse($field->isPublic());
        $this->assertFalse($field->isStatic());
        $this->assertFalse($field->isFinal());
        $this->assertFalse($field->isConst());

        $field = new Field('foo', Field::IS_PUBLIC);

        $this->assertFalse($field->isPrivate());
        $this->assertFalse($field->isProtected());
        $this->assertTrue($field->isPublic());
        $this->assertFalse($field->isStatic());
        $this->assertFalse($field->isFinal());
        $this->assertFalse($field->isConst());

        $field = new Field('foo', Field::IS_STATIC);

        $this->assertTrue($field->isPrivate());
        $this->assertFalse($field->isProtected());
        $this->assertFalse($field->isPublic());
        $this->assertTrue($field->isStatic());
        $this->assertFalse($field->isFinal());
        $this->assertFalse($field->isConst());

        $field = new Field('foo', Field::IS_FINAL);

        $this->assertTrue($field->isPrivate());
        $this->assertFalse($field->isProtected());
        $this->assertFalse($field->isPublic());
        $this->assertFalse($field->isStatic());
        $this->assertTrue($field->isFinal());
        $this->assertFalse($field->isConst());

        $field = new Field('foo', Field::IS_CONST);

        $this->assertTrue($field->isPrivate());
        $this->assertFalse($field->isProtected());
        $this->assertFalse($field->isPublic());
        $this->assertFalse($field->isStatic());
        $this->assertFalse($field->isFinal());
        $this->assertTrue($field->isConst());

        $field = new Field('foo', Field::IS_PUBLIC | Field::IS_STATIC | Field::IS_FINAL);

        $this->assertFalse($field->isPrivate());
        $this->assertFalse($field->isProtected());
        $this->assertTrue($field->isPublic());
        $this->assertTrue($field->isStatic());
        $this->assertTrue($field->isFinal());
        $this->assertFalse($field->isConst());
    }

    public function testCreateFieldWithInvalidVisibility()
    {
        $this->setExpectedException('\InvalidArgumentException', 'Mix of visibilities is not allowed.');

        new Field('foo', Field::IS_PUBLIC | Field::IS_PROTECTED | Field::IS_PRIVATE);
    }
}
