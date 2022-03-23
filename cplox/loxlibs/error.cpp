#include <iostream>
#include "error.h"

Error::Error(){
    hadError=false;
}

void Error::report(int line, string where, string message){
    cerr<<"[line "<<line<< "] Error" <<where <<": "<<message;
    hadError =true;
}

void Error::error(int line, string message){
    report(line,"",message);
}