#include <iostream>
#include <map>
#include "token.h"
#include "scanner.h"
#include "tokentype.h"

using namespace std;

//template <typename T>
Token::Token(TokenType type, string l, string lit, int lin):t(type),lexeme(l),literal(lit),line(lin){
    TokenNames[LEFT_PAREN]="LEFT_PAREN";
  TokenNames[RIGHT_PAREN]="RIGHT_PAREN";
  TokenNames[LEFT_BRACE]="LEFT_BRACE";
  TokenNames[RIGHT_BRACE]="RIGHT_BRACE";
  TokenNames[COMMA]="COMMA"; 
  TokenNames[DOT]="DOT"; 
  TokenNames[MINUS]="MINUS"; 
  TokenNames[PLUS]="PLUS"; 
  TokenNames[SEMICOLON]="SEMICOLON"; 
  TokenNames[SLASH]="SLASH"; 
  TokenNames[STAR]="STAR";

   TokenNames[BANG]="BANG"; 
   TokenNames[BANG_EQUAL]="BANG_EQUAL";
   TokenNames[EQUAL]="EQUAL";
   TokenNames[EQUAL_EQUAL]="EQUAL_EQUAL";
   TokenNames[GREATER]="GREATER"; 
   TokenNames[GREATER_EQUAL]="GREATER_EQUAL";
   TokenNames[LESS]="LESS"; 
   TokenNames[LESS_EQUAL]="LESS_EQUAL";

  
   TokenNames[IDENTIFIER]="IDENTIFIER"; 
   TokenNames[STRING]="STRING";
   TokenNames[NUMBER]="NUMBER";
   TokenNames[AND]="AND"; 
   TokenNames[CLASS]="CLASS";
   TokenNames[ELSE]="ELSE";
   TokenNames[FALSE]="FALSE";
   TokenNames[FUN]="FUN"; 
   TokenNames[FOR]="FOR"; 
   TokenNames[IF]="IF"; 
   TokenNames[NIL]="NIL";
   TokenNames[OR]="OR";
   TokenNames[PRINT]="PRINT";
   TokenNames[RETURN]="RETURN"; 
   TokenNames[SUPER]="SUPER"; 
   TokenNames[THIS]="THIS"; 
   TokenNames[TRUE]="TRUE";
    TokenNames[VAR]="VAR"; 
    TokenNames[WHILE]="WHILE";

    TokenNames[EO_F]="EO_F";

}

Token::~Token(){
    
}

void Token::add(){

}

string Token::toString(){
    return TokenNames.at(t)+" "+lexeme+" "+literal;
}