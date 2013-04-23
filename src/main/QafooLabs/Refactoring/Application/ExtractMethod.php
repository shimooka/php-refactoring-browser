<?php

namespace QafooLabs\Refactoring\Application;

use QafooLabs\Refactoring\Domain\Model\LineRange;
use QafooLabs\Refactoring\Domain\Model\File;
use QafooLabs\Refactoring\Domain\Model\MethodSignature;
use QafooLabs\Refactoring\Domain\Model\EditingSession;
use QafooLabs\Refactoring\Domain\Model\RefactoringException;

use QafooLabs\Refactoring\Domain\Services\ParserScanner;
use QafooLabs\Refactoring\Domain\Services\CodeAnalysis;
use QafooLabs\Refactoring\Domain\Services\Editor;

/**
 * Extract Method Refactoring
 */
class ExtractMethod
{
    /**
     * @var \QafooLabs\Refactoring\Domain\Services\ParserScanner
     */
    private $variableParserScanner;

    /**
     * @var \QafooLabs\Refactoring\Domain\Services\CodeAnalysis
     */
    private $codeAnalysis;

    /**
     * @var \QafooLabs\Refactoring\Domain\Services\Editor
     */
    private $editor;

    public function __construct(ParserScanner $variableParserScanner, CodeAnalysis $codeAnalysis, Editor $editor)
    {
        $this->variableParserScanner = $variableParserScanner;
        $this->codeAnalysis = $codeAnalysis;
        $this->editor = $editor;
    }

    public function refactor(File $file, LineRange $extractRange, $newMethodName)
    {
        if ( ! $this->codeAnalysis->isInsideMethod($file, $extractRange)) {
            throw RefactoringException::rangeIsNotInsideMethod($extractRange);
        }

        $isStatic = $this->codeAnalysis->isMethodStatic($file, $extractRange);
        $methodRange = $this->codeAnalysis->findMethodRange($file, $extractRange);
        $selectedCode = $extractRange->sliceCode($file->getCode());

        $extractVariables = $this->variableParserScanner->scan($file, $extractRange);
        $methodVariables = $this->variableParserScanner->scan($file, $methodRange);

        $buffer = $this->editor->openBuffer($file);

        $newMethod = new MethodSignature(
            $newMethodName,
            $isStatic ? MethodSignature::IS_STATIC : 0,
            $methodVariables->selectionUsedBefore($extractVariables),
            $methodVariables->selectionUsedAfter($extractVariables)
        );

        $session = new EditingSession($buffer);
        $session->replaceRangeWithMethodCall($extractRange, $newMethod);
        $session->addMethod($methodRange->getEnd(), $newMethod, $selectedCode);

        $this->editor->save();
    }
}
