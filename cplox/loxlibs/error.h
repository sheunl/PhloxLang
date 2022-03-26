#ifndef ERROR_H
#define ERROR_H

#include<iostream>

using namespace std;

static bool hadError = false;

class Error{
    public:
    //static bool hadError;

    
    Error();
    ~Error();

    public:
    static void report(int line, string where, string message);
    static void error(int line, string message);
};

//bool Error::hadError=false;

#endif