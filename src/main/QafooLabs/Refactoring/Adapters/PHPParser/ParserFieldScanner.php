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


namespace QafooLabs\Refactoring\Adapters\PHPParser;

use QafooLabs\Refactoring\Domain\Model\LineRange;
use QafooLabs\Refactoring\Domain\Model\File;
use QafooLabs\Refactoring\Domain\Model\DefinedFields;
use QafooLabs\Refactoring\Domain\Services\ParserScanner;

use QafooLabs\Refactoring\Adapters\PHPParser\Visitor\LineRangeStatementCollector;
use QafooLabs\Refactoring\Adapters\PHPParser\Visitor\FieldClassifier;

use PHPParser_Parser;
use PHPParser_Lexer;
use PHPParser_Node;
use PHPParser_Node_Stmt;
use PHPParser_Node_Expr_FuncCall;
use PHPParser_NodeTraverser;

class ParserFieldScanner implements ParserScanner
{
    public function scan(File $file, LineRange $range)
    {
        $parser = new PHPParser_Parser();
        $stmts = $parser->parse(new PHPParser_Lexer($file->getCode()));

        $collector = new LineRangeStatementCollector($range);

        $traverser     = new PHPParser_NodeTraverser;
        $traverser->addVisitor(new \PHPParser_NodeVisitor_NodeConnector);
        $traverser->addVisitor($collector);

        $traverser->traverse($stmts);

        $selectedStatements = $collector->getStatements();

        if ( ! $selectedStatements) {
            throw new \RuntimeException("No statements found in line range.");
        }

        $fieldClassifier = new FieldClassifier();
        $traverser     = new PHPParser_NodeTraverser;
        $traverser->addVisitor($fieldClassifier);
        $traverser->traverse($selectedStatements);

        return new DefinedFields($fieldClassifier->getFields());
    }
}
