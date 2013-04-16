<?php
/**
 * Qafoo PHP Refactoring Browser
 *
 * LICENSE
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.txt.
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to kontakt@beberlei.de so I can send you a copy immediately.
 */

namespace QafooLabs\Refactoring\Adapters\Symfony\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;

use QafooLabs\Refactoring\Domain\Model\File;
use QafooLabs\Refactoring\Domain\Model\Variable;
use QafooLabs\Refactoring\Domain\Model\Field;

use QafooLabs\Refactoring\Application\EncapsulateField;
use QafooLabs\Refactoring\Adapters\PHPParser\ParserVariableScanner;
use QafooLabs\Refactoring\Adapters\TokenReflection\StaticCodeAnalysis;
use QafooLabs\Refactoring\Adapters\Patches\PatchEditor;
use QafooLabs\Refactoring\Adapters\Symfony\OutputPatchCommand;

class EncapsulateFieldCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('encapsulate-field')
            ->setDescription('Convert a class field to an instance field.')
            ->addArgument('file', InputArgument::REQUIRED, 'File that contains a class field.')
            ->addArgument('line', InputArgument::REQUIRED, 'Line of one of the local fields occurrences.')
            ->addArgument('field', InputArgument::REQUIRED, 'Name of the field with or without $.')
            ->setHelp(<<<HELP
If you want to convert a field that is local to a method to an instance field of
that same class, the "convert local to instance field" refactoring helps you with this
task.

<comment>It will:</comment>

1. Convert all occurrences of the same field within the method into an instance field of the same name.
2. Create the instance field on the class.

<comment>Pre-Conditions:</comment>

1. Selected Variable does not exist on class (NOT CHECKED YET)
2. Variable is a local field

<comment>Usage:</comment>

    <info>php refactor.phar encapsulate-field file.php 10 hello</info>

Will convert field \$hello into an instance field \$this->hello.
HELP
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $file = File::createFromPath($input->getArgument('file'), getcwd());
        $line = (int)$input->getArgument('line');
        $field = new Field($input->getArgument('field'));

        $scanner = new ParserVariableScanner();
        $codeAnalysis = new StaticCodeAnalysis();
        $editor = new PatchEditor(new OutputPatchCommand($output));

        $convertRefactoring = new EncapsulateField($scanner, $codeAnalysis, $editor);
        $convertRefactoring->refactor($file, $line, $field);
    }
}
