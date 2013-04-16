<?php

namespace QafooLabs\Refactoring\Application;

use QafooLabs\Refactoring\Domain\Model\File;
use QafooLabs\Refactoring\Domain\Model\LineRange;
use QafooLabs\Refactoring\Domain\Model\Variable;
use QafooLabs\Refactoring\Adapters\PHPParser\ParserVariableScanner;
use QafooLabs\Refactoring\Adapters\TokenReflection\StaticCodeAnalysis;
use QafooLabs\Refactoring\Adapters\Patches\PatchEditor;

class ConvertLocalToInstanceVariableTest extends \PHPUnit_Framework_TestCase
{
    private $applyCommand;

    public function setUp()
    {
        $this->applyCommand = \Phake::mock('QafooLabs\Refactoring\Adapters\Patches\ApplyPatchCommand');

        $scanner = new ParserVariableScanner();
        $codeAnalysis = new StaticCodeAnalysis();
        $editor = new PatchEditor($this->applyCommand);

        $this->refactoring = new ConvertLocalToInstanceVariable($scanner, $codeAnalysis, $editor);
    }

    /**
     * @group integration
     */
    public function testRefactorSimpleMethod()
    {
        $patch = $this->refactoring->refactor(new File("foo.php", <<<'PHP'
<?php
class Calculator
{
    public function calculate($a, $b, $op)
    {
        if ($op === '+') {
            $result = $a + $b;
        }

        return $result;
    }
}
PHP
            ), 7, new Variable("result"));
        \Phake::verify($this->applyCommand)->apply(<<<'CODE'
--- a/foo.php
+++ b/foo.php
@@ -1,5 +1,7 @@
 <?php
 class Calculator
 {
+    private $result;
+
     public function calculate($a, $b, $op)
     {
@@ -5,8 +5,8 @@
     {
         if ($op === '+') {
-            $result = $a + $b;
+            $this->result = $a + $b;
         }
 
-        return $result;
+        return $this->result;
     }
 }
CODE
        );
    }

    /**
     * @group integration
     */
    public function testRefactorSimpleMethod_ThrowRangeIsNotInsideMethod()
    {
        $this->setExpectedException('QafooLabs\Refactoring\Domain\Model\RefactoringException', 'The range 3-3 is not inside one single method.');
        $this->refactoring->refactor(new File("foo.php", <<<'PHP'
<?php
class Calculator
{
    public function calculate($a, $b, $op)
    {
        if ($op === '+') {
            $result = $a + $b;
        }

        return $result;
    }
}
PHP
            ), 3, new Variable("result"));
    }

    /**
     * @group integration
     */
    public function testRefactorSimpleMethod_ThrowVariableNotInRange()
    {
        $this->setExpectedException('QafooLabs\Refactoring\Domain\Model\RefactoringException', 'Could not find variable "$bar" in line range 4-11.');
        $this->refactoring->refactor(new File("foo.php", <<<'PHP'
<?php
class Calculator
{
    public function calculate($a, $b, $op)
    {
        if ($op === '+') {
            $result = $a + $b;
        }

        return $result;
    }
}
PHP
            ), 7, new Variable("bar"));
    }
}
