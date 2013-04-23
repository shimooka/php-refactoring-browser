<?php

namespace QafooLabs\Refactoring\Application;

use QafooLabs\Refactoring\Domain\Model\File;
use QafooLabs\Refactoring\Domain\Model\LineRange;
use QafooLabs\Refactoring\Domain\Model\Variable;
use QafooLabs\Refactoring\Adapters\PHPParser\ParserVariableScanner;
use QafooLabs\Refactoring\Adapters\TokenReflection\StaticCodeAnalysis;
use QafooLabs\Refactoring\Adapters\Patches\PatchEditor;

class RenameLocalVariableTest extends \PHPUnit_Framework_TestCase
{
    private $applyCommand;

    public function setUp()
    {
        $this->applyCommand = \Phake::mock('QafooLabs\Refactoring\Adapters\Patches\ApplyPatchCommand');

        $scanner = new ParserVariableScanner();
        $codeAnalysis = new StaticCodeAnalysis();
        $editor = new PatchEditor($this->applyCommand);

        $this->refactoring = new RenameLocalVariable($scanner, $codeAnalysis, $editor);
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
            ), 7, new Variable("a"), new Variable("c"));
        \Phake::verify($this->applyCommand)->apply(<<<'CODE'
--- a/foo.php
+++ b/foo.php
@@ -2,8 +2,8 @@
 class Calculator
 {
-    public function calculate($a, $b, $op)
+    public function calculate($c, $b, $op)
     {
         if ($op === '+') {
-            $result = $a + $b;
+            $result = $c + $b;
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
            ), 3, new Variable("a"), new Variable("c"));
    }

    /**
     * @group integration
     */
    public function testRefactorSimpleMethod_ThrowVariableNotInRange()
    {
        $this->setExpectedException('QafooLabs\Refactoring\Domain\Model\RefactoringException', 'Could not find variable "$c" in line range 4-11');
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
            ), 7, new Variable("c"), new Variable("d"));
    }
}
