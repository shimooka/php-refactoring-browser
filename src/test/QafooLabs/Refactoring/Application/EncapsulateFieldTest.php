<?php

namespace QafooLabs\Refactoring\Application;

use QafooLabs\Refactoring\Adapters\Patches\PatchEditor;
use QafooLabs\Refactoring\Adapters\PHPParser\ParserVariableScanner;
use QafooLabs\Refactoring\Adapters\TokenReflection\StaticCodeAnalysis;
use QafooLabs\Refactoring\Domain\Model\Field;
use QafooLabs\Refactoring\Domain\Model\File;

class EncapsulateFieldTest extends \PHPUnit_Framework_TestCase
{
    private $applyCommand;

    public function setUp()
    {
        $this->applyCommand = \Phake::mock('QafooLabs\Refactoring\Adapters\Patches\ApplyPatchCommand');

        $scanner = new ParserVariableScanner();
        $codeAnalysis = new StaticCodeAnalysis();
        $editor = new PatchEditor($this->applyCommand);

        $this->refactoring = new EncapsulateField($scanner, $codeAnalysis, $editor);
    }

    /**
     * @group integration
     */
    public function testRefactorPublicField()
    {
        $patch = $this->refactoring->refactor(new File("foo.php", <<<'PHP'
<?php
class Foo
{
    public $public_field;
}
PHP
            ), 4, new Field("public_field"));


        \Phake::verify($this->applyCommand)->apply(<<<'CODE'
--- a/foo.php
+++ b/foo.php
@@ -2,4 +2,14 @@
 class Foo
 {
-    public $public_field;
+    private $public_field;
+
+    public function getPublicField()
+    {
+        return $this->public_field;
+    }
+
+    public function setPublicField($public_field)
+    {
+        $this->public_field = $public_field;
+    }
 }
CODE
        );
    }

    /**
     * @group integration
     */
    public function testRefactorPublicFieldWithMethod()
    {
        $patch = $this->refactoring->refactor(new File("foo.php", <<<'PHP'
<?php
class Foo
{
    public $public_field;

    public function main()
    {
        echo $this->public_field;
    }
}
PHP
            ), 4, new Field("public_field"));


        \Phake::verify($this->applyCommand)->apply(<<<'CODE'
--- a/foo.php
+++ b/foo.php
@@ -2,5 +2,5 @@
 class Foo
 {
-    public $public_field;
+    private $public_field;
 
     public function main()
@@ -7,4 +7,14 @@
     {
         echo $this->public_field;
     }
+
+    public function getPublicField()
+    {
+        return $this->public_field;
+    }
+
+    public function setPublicField($public_field)
+    {
+        $this->public_field = $public_field;
+    }
 }
CODE
        );
    }

    /**
     * @group integration
     */
    public function testRefactorProtectedFieldWithMethod()
    {
        $patch = $this->refactoring->refactor(new File("foo.php", <<<'PHP'
<?php
class Foo
{
    protected $protected_field;

    public function main()
    {
        echo $this->protected_field;
    }
}
PHP
            ), 4, new Field("protected_field"));


        \Phake::verify($this->applyCommand)->apply(<<<'CODE'
--- a/foo.php
+++ b/foo.php
@@ -2,5 +2,5 @@
 class Foo
 {
-    protected $protected_field;
+    private $protected_field;
 
     public function main()
@@ -7,4 +7,14 @@
     {
         echo $this->protected_field;
     }
+
+    public function getProtectedField()
+    {
+        return $this->protected_field;
+    }
+
+    public function setProtectedField($protected_field)
+    {
+        $this->protected_field = $protected_field;
+    }
 }
CODE
        );
    }

    /**
     * @group integration
     */
    public function testRefactorPrivateFieldWithMethod()
    {
        $patch = $this->refactoring->refactor(new File("foo.php", <<<'PHP'
<?php
class Foo
{
    private $private_field;

    public function main()
    {
        echo $this->private_field;
    }
}
PHP
            ), 4, new Field("private_field"));


        \Phake::verify($this->applyCommand)->apply(<<<'CODE'
--- a/foo.php
+++ b/foo.php
@@ -2,5 +2,5 @@
 class Foo
 {
-    private $private_field;
+    private $private_field;
 
     public function main()
@@ -7,4 +7,14 @@
     {
         echo $this->private_field;
     }
+
+    public function getPrivateField()
+    {
+        return $this->private_field;
+    }
+
+    public function setPrivateField($private_field)
+    {
+        $this->private_field = $private_field;
+    }
 }
CODE
        );
    }

    /**
     * @group integration
     */
    public function testRefactor_ThrowRangeIsNotOutsideMethod()
    {
        $this->setExpectedException('QafooLabs\Refactoring\Domain\Model\RefactoringException', 'The range 7-7 is not outside of methods');
        $this->refactoring->refactor(new File("foo.php", <<<'PHP'
<?php
class Foo
{
    public $public_field;

    public function main()
    {
        echo $this->public_field;
    }
}
PHP
            ), 7, new Field("public_field"));
    }

    /**
     * @group integration
     */
    public function testRefactor_ThrowVariableNotInRange()
    {
        $this->setExpectedException('QafooLabs\Refactoring\Domain\Model\RefactoringException', 'The range 7-7 is not outside one single method');
        $this->refactoring->refactor(new File("foo.php", <<<'PHP'
<?php
class Foo
{
    public $public_field;

    public function main()
    {
        echo $this->public_field;
    }
}
PHP
            ), 4, new Field("bar"));
    }
}
