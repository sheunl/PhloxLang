#include <iostream>
#include <map>
#include "token.h"
#include "tokentype.h"

using namespace std;

template <typename T>
Token::Token(TokenType type, string l, T lit, int lin):t(type),lexeme(l),literal(lit),line(lin){
    
}

Token::~Token(){
    
}

void Token::add(){

}