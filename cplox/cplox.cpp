#include <iostream>
#include <sstream>
#include <fstream>
#include <list>
#include <string>
#include "loxlibs/scanner.h"
#include "loxlibs/token.h"
#include "loxlibs/error.h"


//#define TEST_MODE

using namespace std;



class Lox {
    
    
    public:
    Lox(){
    }

    static void runFile(string path){  
        #ifdef TEST_MODE
       cout<<"\nReading File: "<<__FUNCTION__<<"\n";
       #endif
        
       ifstream ScriptFile(path);
       string buf;
      /* stringstream buf;

        buf<<ScriptFile.rdbuf();
        
        run(buf.str());

        ScriptFile.close();
        */

       if(!ScriptFile.is_open()){
           cerr<<"Error: Could not open file.";
           exit(65);
       }

       while(ScriptFile.peek()!=EOF){
           ScriptFile>>buf;
           run(buf);
           buf="";
           //cout<<buf<<"\n";
       }

       ScriptFile.close();

        if (hadError) exit(65);

    } 

    static void runPrompt(){
        #ifdef TEST_MODE
        cout<<"\nRunning Prompt:<<__FUNCTION__<<"\n";
        #endif

        string line;
        for(;;){
            cout<<">";
            cin>>line;
            if(cin.eof()) break;
            run(line);
            hadError=false;
            
        }

    }


    static void run(string source){
        #ifdef TEST_MODE
        cout<<"\n"<<source in function:<<__FUNCTION__<<"\n";
        #endif

       Scanner s(source);
       list<Token> one =s.scanTokens();

        for(list<Token>::iterator it= one.begin(); it!= one.end();it++){
            cout<< it->toString() <<"\n";
        }

        cout<<endl;

    }

    
};



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