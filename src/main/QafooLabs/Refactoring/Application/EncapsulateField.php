<?php

namespace QafooLabs\Refactoring\Application;

use QafooLabs\Refactoring\Domain\Model\File;
use QafooLabs\Refactoring\Domain\Model\LineRange;
use QafooLabs\Refactoring\Domain\Model\MethodSignature;
use QafooLabs\Refactoring\Domain\Model\Field;

use QafooLabs\Refactoring\Domain\Model\RefactoringException;
use QafooLabs\Refactoring\Domain\Model\EditingSession;

use QafooLabs\Refactoring\Domain\Services\VariableScanner;
use QafooLabs\Refactoring\Domain\Services\CodeAnalysis;
use QafooLabs\Refactoring\Domain\Services\Editor;

class EncapsulateField
{
    /**
     * @var \QafooLabs\Refactoring\Domain\Services\VariableScanner
     */
    private $variableScanner;

    /**
     * @var \QafooLabs\Refactoring\Domain\Services\CodeAnalysis
     */
    private $codeAnalysis;

    /**
     * @var \QafooLabs\Refactoring\Domain\Services\Editor
     */
    private $editor;

    public function __construct(VariableScanner $variableScanner, CodeAnalysis $codeAnalysis, Editor $editor)
    {
        $this->variableScanner = $variableScanner;
        $this->codeAnalysis = $codeAnalysis;
        $this->editor = $editor;
    }

    public function refactor(File $file, $line, Field $convertField)
    {
        $range = LineRange::fromSingleLine($line);
        if ($this->codeAnalysis->isInsideMethod($file, $range)) {
            throw RefactoringException::rangeIsNotOutsideMethod($range);
        }

//        $isStatic = $this->codeAnalysis->isFieldStatic($file, LineRange::fromSingleLine($line));
//        $getMethod = $convertField->convertToGetter();
//        $lastPropertyLine = $this->codeAnalysis->getLineOfLastPropertyDefined($file);

//        $selectedMethodLineRange = $this->codeAnalysis->findMethodRange($file, LineRange::fromSingleLine($line));
//        $definedFields = $this->variableScanner->scanForFields($file);

//        if ( ! $definedFields->contains($convertField)) {
//            throw RefactoringException::variableNotInRange($convertField, $selectedMethodLineRange);
//        }

        $buffer = $this->editor->openBuffer($file);

        $session = new EditingSession($buffer);
        $session->replaceLineWithProperty(LineRange::fromSingleLine($line), $convertField);
//        $session->replaceString($definedVariables, $convertField, $getMethod);
//        $session->replaceRangeWithMethodCall($file->getLineRange(), $convertField, $getMethod);

        $getterMethod = new MethodSignature(
            'get' . $convertField->getCamelName(),
//            MethodSignature::IS_PUBLIC + ($isStatic ? MethodSignature::IS_STATIC : 0),
            MethodSignature::IS_PUBLIC
        );
        $code = sprintf('return $this->%s;', $convertField->getName());
        $session->addMethod(
            $this->codeAnalysis->getLineOfLastMethodDefinedEndLine($file, $range),
            $getterMethod,
            array($code)
        );

        $setterMethod = new MethodSignature(
            'set' . $convertField->getCamelName(),
//            MethodSignature::IS_PUBLIC + ($isStatic ? MethodSignature::IS_STATIC : 0),
            MethodSignature::IS_PUBLIC,
            array($convertField->getName())
        );
        $code = sprintf('$this->%s = $%s;', $convertField->getName(), $convertField->getName());
        $session->addMethod(
            $this->codeAnalysis->getLineOfLastMethodDefinedEndLine($file, $range),
            $setterMethod,
            array($code)
        );

        $this->editor->save();
    }
}

