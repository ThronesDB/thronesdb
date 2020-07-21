<?php

namespace App\DQL;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

/**
 * "POWER" "(" IntegerPrimary "," IntegerPrimary ")"
 */
class PowerFunction extends FunctionNode
{
    public $basePrimary;
    public $exponentPrimary;

    /**
     * @inheritdoc
     */
    public function getSql(SqlWalker $sqlWalker)
    {
        return sprintf(
            "POW(%s,%d)",
            $this->basePrimary->dispatch($sqlWalker),
            $this->exponentPrimary->dispatch($sqlWalker)
        );
    }

    /**
     * @inheritdoc
     */
    public function parse(Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->basePrimary = $parser->StringExpression();
        $parser->match(Lexer::T_COMMA);
        $this->exponentPrimary = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }
}
