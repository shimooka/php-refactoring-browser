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

use QafooLabs\Refactoring\Application\EncapsulateField;
use QafooLabs\Refactoring\Adapters\PHPParser\ParserFieldScanner;
use QafooLabs\Refactoring\Adapters\TokenReflection\StaticCodeAnalysis;
use QafooLabs\Refactoring\Adapters\Patches\PatchEditor;
use QafooLabs\Refactoring\Adapters\Symfony\OutputPatchCommand;

class EncapsulateFieldCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('encapsulate-field')
            ->setDescription('Make a public field field private and privide accessors.')
            ->addArgument('file', InputArgument::REQUIRED, 'File that contains a class field.')
            ->addArgument('line', InputArgument::REQUIRED, 'Line of the field.')
            ->addArgument('field', InputArgument::REQUIRED, 'Name of the field with or without $.')
            ->setHelp(<<<HELP
If you want to Make a field private and privide accessors,
the "encapsulate field" refactoring helps you with this task.

<comment>It will:</comment>

1. Make the field private.
2. Create accessors (getXxx/setXxx methods) on the class to access the field.

<comment>Pre-Conditions:</comment>

1. Accessors do not exist on class (NOT CHECKED YET)
2. Variable is a field

<comment>Usage:</comment>

    <info>php refactor.phar encapsulate-field file.php 10 hello</info>

Will make the field \$hello private and add getHello()/setHello(\$hello) methods.
HELP
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $file = File::createFromPath($input->getArgument('file'), getcwd());
        $line = (int)$input->getArgument('line');
        $field = $input->getArgument('field');

        $scanner = new ParserFieldScanner();
        $codeAnalysis = new StaticCodeAnalysis();
        $editor = new PatchEditor(new OutputPatchCommand($output));

        $convertRefactoring = new EncapsulateField($scanner, $codeAnalysis, $editor);
        $convertRefactoring->refactor($file, $line, $field);
    }
}
