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


namespace QafooLabs\Refactoring\Adapters\PHPParser\Visitor;

use PHPParser_Node;
use PHPParser_NodeVisitorAbstract;
use PHPParser_Node_Stmt_Property;
use PHPParser_Node_Stmt_PropertyProperty;

/**
 * Classify fields into assignments and usages,
 * permanent and temporary fields.
 */
class FieldClassifier extends PHPParser_NodeVisitorAbstract
{
    private $fields = array();

    public function enterNode(PHPParser_Node $node)
    {
        if ($node instanceof PHPParser_Node_Stmt_Property) {
            foreach ($node->props as $node) {
                if ($node instanceof PHPParser_Node_Stmt_PropertyProperty) {
                    $this->enterFieldNode($node);
                    break;
                }
            }
        }
    }

    private function enterFieldNode($node)
    {
        $this->fields[$node->name][] = $node->getLine();
    }

    public function getFields()
    {
        return $this->fields;
    }

}
