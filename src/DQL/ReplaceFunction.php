<?php

namespace App\DQL;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\Node;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

/**
 * @package App\DQL
 */
class ReplaceFunction extends FunctionNode
{
    /**
     * @var Node $stringPrimary
     */
    public $stringPrimary;

    /**
     * @var Node $stringSecondary
     */
    public $stringSecondary;

    /**
     * @var Node $stringThird
     */
    public $stringThird;

    /**
     * @inheritdoc
     */
    public function getSql(SqlWalker $sqlWalker)
    {
        return 'REPLACE(' .
                    $this->stringPrimary->dispatch($sqlWalker) . ', ' .
                    $this->stringSecondary->dispatch($sqlWalker) . ', ' .
                    $this->stringThird->dispatch($sqlWalker) .
                ')';
    }

    /**
     * @inheritdoc
     */
    public function parse(Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->stringPrimary = $parser->StringPrimary();
        $parser->match(Lexer::T_COMMA);
        $this->stringSecondary = $parser->StringPrimary();
        $parser->match(Lexer::T_COMMA);
        $this->stringThird = $parser->StringPrimary();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }
}
