#ifndef TOKEN_H
#define TOKEN_H

#include <iostream>
#include<map>
#include "tokentype.h"
using namespace std;


//for represrnting "Object literal"
template <typename T>
class Object{

    public:
    string toString(){
        return this;
    }

};

static map<TokenType,string> TokenNames;




class Token{

    

    public:
    const TokenType t;
    const string lexeme;
    const string literal;
    const int line;

    public:
    //template <typename T>
    Token(TokenType type, string lexeme, string literal, int line);
    ~Token();
    void add();
    string toString();

};




#endif