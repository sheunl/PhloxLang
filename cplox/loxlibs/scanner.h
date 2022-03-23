#ifndef SCANNER_H
#define SCANNER_H

#include <iostream>
#include <algorithm>
#include <array>
#include <list>
#include "token.h"

using namespace std;

class Scanner
{
private:
    const string source;
    list <Token> tokens;
    int start =0;
    int current = 0;
    int line = 1;
public:
    Scanner(string source);
    ~Scanner();
    list<Token> scanTokens();
    bool isAtEnd();
    void scanToken();
    char advance();
    void addToken(TokenType type);
    template <typename T>
    void addToken(TokenType type, T literal);
    bool match(char expected);
    char peek();
    void astring();
    bool isDigit(char c);
    void number();
    char peekNext();
    void identifier();
    bool isAlpha(char c);
    bool isAlphaNumeric(char c);
};



#endif