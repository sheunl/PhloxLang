#include <iostream>
#include <sstream>
#include <fstream>
#include <list>
#include "loxlibs/scanner.h"
#include "loxlibs/token.h"

using namespace std;

bool hadError = false;

class Lox {
    
    
    public:
    Lox(){
    }

    static void runFile(string path){   
       cout<<"Reading File\n";
        
       ifstream ScriptFile(path);
       stringstream buf;

        buf<<ScriptFile.rdbuf();
        
        run(buf.str());
        ScriptFile.close();

    } 

    static void runPrompt(){
        cout<<"Running Prompt\n";

        string line;
        for(;;){
            cout<<">";
            cin>>line;
            if(cin.eof()) break;
            run(line);
            
        }

    }

   // static ;

    static void run(string source){
        cout<<"\n"<<source<<"\n";

       Scanner s(source);
       list<Token> one;

        for(list<Token>::iterator it= one.begin(); it!= one.end();it++){
            cout<< &it <<" ";
        }

        cout<<endl;
     //   for ();

    }

    
};

void report(int line, string where, string message){
    cerr<<"[line "<<line<< "] Error" <<where <<": "<<message;
    hadError =true;
}

void error(int line, string message){
    report(line,"",message);
}


int main(int argc, char** argv){
    
    if(argc >2){
        cout<<"Usage: cplox [script]"<<endl;
        exit(0);
    } else if (argc == 2){
        //cout<<argv[1];
        Lox::runFile(argv[1]);
    } else {
        Lox::runPrompt();
    }
}