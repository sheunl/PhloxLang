#include <iostream>
#include <algorithm>
#include <list>
#include <string>
#include <map>
#include <unordered_map>
#include <typeinfo>
#include "tokentype.h"
#include "token.h"
#include "scanner.h"
#include "error.h"

using namespace std;




Scanner::Scanner(string s):source(s)
{
    #ifdef TEST_MODE
    cout<<"\nscanner:"<<__FUNCTION__<<"Source:"<<source<<endl;
    #endif

keywords.insert(pair<string,TokenType>("and",AND));
keywords.insert(pair<string,TokenType>("class",CLASS));
keywords.insert(pair<string,TokenType>("else",ELSE));
keywords.insert(pair<string,TokenType>("false",FALSE));
keywords.insert(pair<string,TokenType>("for",FOR));
keywords.insert(pair<string,TokenType>("fun",FUN));
keywords.insert(pair<string,TokenType>("if",IF));
keywords.insert(pair<string,TokenType>("nil",NIL));
keywords.insert(pair<string,TokenType>("or",OR));
keywords.insert(pair<string,TokenType>("print",PRINT));
keywords.insert(pair<string,TokenType>("return",RETURN));
keywords.insert(pair<string,TokenType>("super",SUPER));
keywords.insert(pair<string,TokenType>("this",THIS));
keywords.insert(pair<string,TokenType>("true",TRUE));
keywords.insert(pair<string,TokenType>("var",VAR));
keywords.insert(pair<string,TokenType>("while",WHILE));

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
    string ov="";
    Token send(type,text,ov,line);
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
    TokenType type=IDENTIFIER;
    if(keywords.find(text)==keywords.end()){
     type = IDENTIFIER; 
    }else{
        type = keywords.find(text)->second; 
    }
    //TokenType type =IF;
    
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
    return false;
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