import sys
from scanner import Scanner
from errors import Error 

Error.errormsg = False

def start(args=sys.argv):
    if len(args)>2:
        print ("Usage: pylox [script]")
        exit()
    elif len(args)==2:
        runFile(args[1])
    else:
        runPrompt()


def runFile(a):
    f = open(a)
    l=f.read()
    run(l)
    print("File: "+ l)

def runPrompt():
    print ("Prompt is running...")

    while True:
        l=input('> ')
        if l==None:
            break
        run(l)
        Error.errormsg = False

def run(a):
    s =Scanner(a)
    tokens = s.scanTokens()
    for x in tokens:
        print(x)
    if Error.errormsg:
        exit()
    return 0




start()
