import sys


def start(args=sys.argv):
    if len(args)>2:
        print ("Usage: pylox [script]")
        exit()
    elif len(args)==2:
        runFile(args[1])
    else:
        runPrompt()

def runPrompt():
    print ("Prompt is running...")

def runFile(a):
    print("File: "+ a)

start()
