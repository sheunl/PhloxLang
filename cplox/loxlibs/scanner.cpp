#include <iostream>
#include <algorithm>
#include <list>
#include <string>
#include <map>
#include "tokentype.h"
#include "scanner.h"
#include "error.h"

using namespace std;

static map<string,TokenType> keywords;
/*
keywords.emplace("and",AND);
keywords.emplace("class",CLASS);
keywords.emplace("else",ELSE);
keywords.emplace("false",FALSE);
keywords.emplace("for",FOR);
keywords.emplace("fun",FUN);
keywords.emplace("if",IF);
keywords.emplace("nil",NIL);
keywords.emplace("or",OR);
keywords.emplace("print",PRINT);
keywords.emplace("return",RETURN);
keywords.emplace("super",SUPER);
keywords.emplace("this",THIS);
keywords.emplace("true",TRUE);
keywords.emplace("var",VAR);
keywords.emplace("while",WHILE); */



Scanner::Scanner(string s):source(s)
{
    cout<<"Scanner is Called! with source:\n"<<source<<endl;

}

bool Scanner::isAtEnd(){
    return current >= source.length();
}

char Scanner::advance(){
    return source.at(current++);
}

void Scanner::addToken(TokenType type){
    addToken(type, NULL);
}

template <typename T>
void Scanner::addToken(TokenType type, T literal)
{
    string text = source.substr(start,current);
    Token send(type,text,literal,line);
    tokens.push_back(send);
}

list<Token> Scanner::scanTokens(){
    while(!isAtEnd()){
        start = current;
        scanToken();
    }

    Token send(EO_F,"","",line);
    tokens.push_back(send);
    return tokens;
}

void Scanner::scanToken(){
    char c = advance();
    switch(c){
        case '(': addToken(LEFT_PAREN); break;
        case ')': addToken(RIGHT_PAREN); break;
        case '{': addToken(LEFT_BRACE); break;
        case '}': addToken(RIGHT_BRACE); break;
        case ',': addToken(COMMA); break;
        case '.': addToken(DOT); break;
        case '-': addToken(MINUS); break;
        case '+': addToken(PLUS); break;
        case ';': addToken(SEMICOLON); break;
        case '*': addToken(STAR); break;
        case '!':
        addToken(match('=') ? BANG_EQUAL : BANG);
        break;
      case '=':
        addToken(match('=') ? EQUAL_EQUAL : EQUAL);
        break;
      case '<':
        addToken(match('=') ? LESS_EQUAL : LESS);
        break;
      case '>':
        addToken(match('=') ? GREATER_EQUAL : GREATER);
        break;
        case '/':
        if (match('/')) {
          // A comment goes until the end of the line.
          while (peek() != '\n' && !isAtEnd()) advance();
        } else {
          addToken(SLASH);
        }
        break;
        case ' ':
        case '\r':
        case '\t':
        // Ignore whitespace.
        break;

        case '\n':
        line++;
        break;

        case '"':astring();break;

        case 'o':
            if(match('r')){
                addToken(OR);
            }
        break;
        default:
            if(isDigit(c)){
                number();
            } else if(isAlpha(c)){
                identifier();
            } else {
            Error::error(line,"Unexpected character.");
            }
            break;

    }
}

void Scanner::identifier(){
    while (isAlphaNumeric(peek())) advance();
    string text = source.substr(start, current);
    TokenType type = keywords.at(text);
    if (type == NULL) type = IDENTIFIER;
    addToken(type);
}

void Scanner::astring(){
    while(peek() != '"' && !isAtEnd()){
        if(peek()=='\n') line++;
        advance();
    }

    if(isAtEnd()){
        Error::error(line,"Unterminated String");
        return;
    }

    advance();

    string value =source.substr(start+1,current-1);
    addToken(STRING,value);
}

bool Scanner::match(char expected){
    if(isAtEnd()) return false;
    if( source.at(current) != expected) return false;
}

char Scanner::peek(){
    if (isAtEnd()) return '\0';
    return source.at(current);
}

char Scanner::peekNext(){
    if(current + 1 >= source.length()) return '\0';
    return source.at(current+1);
}

bool Scanner::isAlpha(char c){
    return (c >= 'a' && c <= 'z') || (c >= 'A' && c <='Z') || (c=='_');
}

bool Scanner::isAlphaNumeric(char c){
    return isAlpha(c) || isDigit(c);
}

bool Scanner::isDigit(char c){
    return c >='0' && c <='9';
}

void Scanner::number(){
    while (isDigit(peek())) advance();
    
    if(peek()=='.' && isDigit(peekNext())){
        advance();

        while(isDigit(peek())) advance();
    }
    
    addToken(NUMBER, stod(source.substr(start,current)));
}


Scanner::~Scanner()
{
}