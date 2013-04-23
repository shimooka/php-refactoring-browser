<?php

namespace QafooLabs\Refactoring\Application;

use QafooLabs\Refactoring\Domain\Model\File;
use QafooLabs\Refactoring\Domain\Model\LineRange;
use QafooLabs\Refactoring\Domain\Model\MethodSignature;
use QafooLabs\Refactoring\Domain\Model\Field;

use QafooLabs\Refactoring\Domain\Model\RefactoringException;
use QafooLabs\Refactoring\Domain\Model\EditingSession;

use QafooLabs\Refactoring\Domain\Services\ParserScanner;
use QafooLabs\Refactoring\Domain\Services\CodeAnalysis;
use QafooLabs\Refactoring\Domain\Services\Editor;

class EncapsulateField
{
    /**
     * @var \QafooLabs\Refactoring\Domain\Services\ParserScanner
     */
    private $fieldParserScanner;

    /**
     * @var \QafooLabs\Refactoring\Domain\Services\CodeAnalysis
     */
    private $codeAnalysis;

    /**
     * @var \QafooLabs\Refactoring\Domain\Services\Editor
     */
    private $editor;

    public function __construct(ParserScanner $fieldParserScanner, CodeAnalysis $codeAnalysis, Editor $editor)
    {
        $this->fieldParserScanner = $fieldParserScanner;
        $this->codeAnalysis = $codeAnalysis;
        $this->editor = $editor;
    }

    public function refactor(File $file, $line, $fieldName)
    {
        $range = LineRange::fromSingleLine($line);
        if ($this->codeAnalysis->isInsideMethod($file, $range)) {
            throw RefactoringException::rangeIsNotOutsideMethod($range);
        }

        $definedFields = $this->fieldParserScanner->scan($file, $range);

        if ( ! $definedFields->contains(new Field($fieldName))) {
            throw RefactoringException::fieldNotInRange($fieldName, $range);
        }

        $isStatic = $this->codeAnalysis->isFieldStatic($file, LineRange::fromSingleLine($line));
        $lineOfLastMethodEndLine = $this->codeAnalysis->getLineOfLastMethodEndLine($file, $range);
        $field = new Field($fieldName, MethodSignature::IS_PRIVATE + ($isStatic ? MethodSignature::IS_STATIC : 0));

        $buffer = $this->editor->openBuffer($file);

        $session = new EditingSession($buffer);
        $session->replaceLineWithProperty(LineRange::fromSingleLine($line), $field);

        $getterMethod = new MethodSignature(
            'get' . $field->getCamelName(),
            MethodSignature::IS_PUBLIC + ($isStatic ? MethodSignature::IS_STATIC : 0)
        );
        $code = sprintf('return %s%s;',
            ($isStatic ? 'self::$' : '$this->'), $field->getName());
        $session->addMethod(
            $lineOfLastMethodEndLine,
            $getterMethod,
            array($code)
        );

        $setterMethod = new MethodSignature(
            'set' . $field->getCamelName(),
            MethodSignature::IS_PUBLIC + ($isStatic ? MethodSignature::IS_STATIC : 0),
            array($field->getName())
        );
        $code = sprintf('%s%s = $%s;', 
            ($isStatic ? 'self::$' : '$this->'),
            $field->getName(), $field->getName());
        $session->addMethod(
            $lineOfLastMethodEndLine,
            $setterMethod,
            array($code)
        );

        $this->editor->save();
    }
}

