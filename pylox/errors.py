class Error:

    errormsg =False

    def error(line, msg):
        Error.report(line,"", msg)

    def report(line,where,msg):
        print("[Line "+str(line)+ "] Error "+ where+": "+msg )
        
        Error.errormsg=True