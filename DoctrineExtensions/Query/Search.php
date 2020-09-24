<?php

namespace Requestum\ApiBundle\DoctrineExtensions\Query;

use Doctrine\ORM\Query\AST\Functions\FunctionNode,
    Doctrine\ORM\Query\Lexer;

class Search extends FunctionNode
{
    public $fieldExpression = null;
    public $likeExpression = null;

    public function parse(\Doctrine\ORM\Query\Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->fieldExpression = $parser->ArithmeticExpression();
        $parser->match(Lexer::T_COMMA);
        $this->likeExpression = $parser->StringPrimary();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    /**
     * @example
     * SEARCH(<field_name>,<like_value>) = true
     *
     * @param \Doctrine\ORM\Query\SqlWalker $sqlWalker
     * @return string
     */
    public function getSql(\Doctrine\ORM\Query\SqlWalker $sqlWalker)
    {
        $params = $sqlWalker->getConnection()->getParams();
        switch ($params['driver']) {
            case 'pdo_pgsql':
                $sql = '(' . $this->fieldExpression->dispatch($sqlWalker) . ' ILIKE ' . $this->likeExpression->dispatch($sqlWalker) . ')';
                break;
            default:
                $sql = '(' . $this->fieldExpression->dispatch($sqlWalker) . ' LIKE ' . $this->likeExpression->dispatch($sqlWalker) . ')';
                break;
        }

        return $sql;
    }
}
