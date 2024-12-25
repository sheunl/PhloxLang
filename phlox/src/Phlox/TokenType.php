<?php
namespace Phlox;

/**
 * TokenType defines all possible token types in the language
 * These constants are used to identify different elements of the syntax
 */
class TokenType{
    // Single character tokens
    /** Left parenthesis '(' */
    const LEFT_PAREN = 'LEFT_PAREN';
    /** Right parenthesis ')' */
    const RIGHT_PAREN = 'RIGHT_PAREN';
    /** Left brace '{' */
    const LEFT_BRACE = 'LEFT_BRACE'; 
    /** Right brace '}' */
    const RIGHT_BRACE = 'RIGHT_BRACE';
    /** Comma ',' */
    const COMMA = 'COMMA';
    /** Dot '.' */
    const DOT = 'DOT';
    /** Minus '-' */
    const MINUS = 'MINUS';
    /** Plus '+' */
    const PLUS = 'PLUS';
    /** Semicolon ';' */
    const SEMICOLON = 'SEMICOLON';
    /** Forward slash '/' */
    const SLASH = 'SLASH';
    /** Star '*' */
    const STAR = 'STAR';

    // One or two character tokens
    /** Bang '!' */
    const BANG = 'BANG';
    /** Not equal '!=' */
    const BANG_EQUAL = 'BANG_EQUAL';
    /** Assignment '=' */
    const EQUAL = 'EQUAL'; 
    /** Equality '==' */
    const EQUAL_EQUAL = 'EQUAL_EQUAL';
    /** Greater than '>' */
    const GREATER = 'GREATER'; 
    /** Greater than or equal '>=' */
    const GREATER_EQUAL = 'GREATER_EQUAL';
    /** Less than '<' */
    const LESS = 'LESS';
    /** Less than or equal '<=' */
    const LESS_EQUAL = 'LESS_EQUAL';

    // Literals
    /** Variable or function names */
    const IDENTIFIER = 'IDENTIFIER'; 
    /** String literal */
    const STRING = 'STRING';
    /** Numeric literal */
    const NUMBER = 'NUMBER';

    // Keywords
    /** Logical AND operator */
    const AND = 'AND'; 
    /** Class declaration */
    const ACLASS = 'ACLASS';
    /** Else branch of conditional */
    const ELSE = 'ELSE';
    /** Boolean false */
    const FALSE = 'FALSE';
    /** Function declaration */
    const FUN = 'FUN';
    /** For loop */
    const FOR = 'FOR';
    /** If conditional */
    const IF = 'IF';
    /** Null value */
    const NIL = 'NIL';
    /** Logical OR operator */
    const OR = 'OR';
    /** Print statement */
    const PRINT = 'PRINT';
    /** Return statement */
    const RETURN = 'RETURN';
    /** Super class reference */
    const SUPER = 'SUPER';
    /** This reference */
    const THIS = 'THIS';
    /** Boolean true */
    const TRUE = 'TRUE';
    /** Variable declaration */
    const VAR = 'VAR';
    /** While loop */
    const WHILE = 'WHILE';

    /** End of file token */
    const EOF = 'EOF';
}