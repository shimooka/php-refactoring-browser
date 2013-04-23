<?php

namespace QafooLabs\Refactoring\Application;

use QafooLabs\Refactoring\Domain\Model\File;
use QafooLabs\Refactoring\Domain\Model\Variable;
use QafooLabs\Refactoring\Domain\Model\DefinedVariables;
use QafooLabs\Refactoring\Domain\Model\LineRange;
use QafooLabs\Refactoring\Domain\Model\RefactoringException;
use QafooLabs\Refactoring\Domain\Model\EditingSession;

use QafooLabs\Refactoring\Domain\Services\ParserScanner;
use QafooLabs\Refactoring\Domain\Services\CodeAnalysis;
use QafooLabs\Refactoring\Domain\Services\Editor;

/**
 * Rename Local Variable Refactoring
 */
class RenameLocalVariable
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

    public function refactor(File $file, $line, Variable $oldName, Variable $newName)
    {
        if ( ! $this->codeAnalysis->isInsideMethod($file, LineRange::fromSingleLine($line))) {
            throw RefactoringException::rangeIsNotInsideMethod(LineRange::fromSingleLine($line));
        }

        if ( ! $oldName->isLocal()) {
            throw RefactoringException::variableNotLocal($oldName);
        }

        if ( ! $newName->isLocal()) {
            throw RefactoringException::variableNotLocal($newName);
        }

        $selectedMethodLineRange = $this->codeAnalysis->findMethodRange($file, LineRange::fromSingleLine($line));
        $definedVariables = $this->variableParserScanner->scan(
            $file, $selectedMethodLineRange
        );

        if ( ! $definedVariables->contains($oldName)) {
            throw RefactoringException::variableNotInRange($oldName, $selectedMethodLineRange);
        }

        $buffer = $this->editor->openBuffer($file);

        $session = new EditingSession($buffer);
        $session->replaceString($definedVariables, $oldName, $newName);

        $this->editor->save();
    }
}

