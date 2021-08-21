from TokenType import T
from Token import Token
from errors import Error 

class Scanner:

    KEYWORDS={}
    KEYWORDS["and"]=T.AND
    KEYWORDS["class"]=T.CLASS
    KEYWORDS["else"]=T.ELSE
    KEYWORDS["false"]=T.FALSE
    KEYWORDS["for"]=T.FOR
    KEYWORDS["fun"]=T.FUN
    KEYWORDS["if"]=T.IF
    KEYWORDS["nil"]=T.NIL
    KEYWORDS["or"]=T.OR
    KEYWORDS["print"]=T.PRINT
    KEYWORDS["return"]=T.RETURN
    KEYWORDS["super"]=T.SUPER
    KEYWORDS["this"]=T.THIS
    KEYWORDS["true"]=T.TRUE
    KEYWORDS["var"]=T.VAR
    KEYWORDS["while"]=T.WHILE
    

    def __init__(self,source='',start=0,current=0,line=1) -> None:
        self.TOKENS=[]
        self.source=source
        self.start=start
        self.current=current
        self.line=line
        
        
    
    def scanTokens(self):
        print('scanTokens')
        while not self.isAtEnd():
            self.start =self.current
            self.scanToken()
        
        self.TOKENS.append( Token(T.EOF,"",None,self.line) )
        return self.TOKENS

    def scanToken(self):
        print('scanToken')
        c=self.advance()
        if c == '(': 
            self.addToken(T.LEFT_PAREN)
        elif c == ')': 
            self.addToken(T.RIGHT_PAREN)
        elif c == '}': 
            self.addToken(T.RIGHT_BRACE)
        elif c == '{': 
            self.addToken(T.LEFT_BRACE)
        elif c == ',': 
            self.addToken(T.COMMA)
        elif c == '.': 
            self.addToken(T.DOT)
        elif c == '-': 
            self.addToken(T.MINUS)
        elif c == '+': 
            self.addToken(T.PLUS)
        elif c == ';': 
            self.addToken(T.SEMICOLON)
        elif c == '*': 
            self.addToken(T.STAR)
        elif c == '!': 
            self.addToken(T.BANG_EQUAL if self.match('=') else T.BANG)
        elif c == '=': 
            self.addToken(T.EQUAL_EQUAL if self.match('=') else T.EQUAL)
        elif c == '<': 
            self.addToken(T.LESS_EQUAL if self.match('=') else T.LESS )
        elif c == '>': 
            self.addToken(T.GREATER_EQUAL if self.match('=') else T.GREATER)
        elif c == '/':
            if self.match('/') :
                while self.peek() != '\n' and not self.isAtEnd():
                    self.advance()
            else:
                self.addToken(T.SLASH) 
        elif c == ' ': 
            pass
        elif c == '\r': 
            pass
        elif c == '\t': 
            pass
        elif c == 'o': 
            if self.match('r'):
                self.addToken(T.OR)
        elif c == '\n': 
            self.line+=1
        elif c=='"':
            self.string()
        else:
            if self.isDigit(c):
                self.number()
            elif self.isAlpha(c):
                self.identifier()
            else:
                Error.error(self.line,"Unexpected Charcter!")

    def identifier(self):
        print('identifier')
        while self.isAlphaNumeric(self.peek()):
            self.advance()
        
        text = self.source[self.start:self.current]
        type = self.KEYWORDS.get(text)
        if type==None:
            type = T.IDENTIFIER
        self.addToken(type)
        #self.addToken(T.IDENTIFIER)
    
    def number(self):
        print('number')
        while self.isDigit(self.peek()):
            self.advance()
        
        if self.peek() == '.' and self.isDigit(self.peekNext()):
            self.advance()
            while self.isDigit(self.peek()):
                self.advance()
        
        self.addToken(T.NUMBER,float(self.source[self.start:self.current]) )

    def string(self):
        print('string')
        while self.peek() != '"' and not self.isAtEnd():
            if self.peek() == '\n':
                self.line+=1
            self.advance()

        if self.isAtEnd():
            Error.error(self.line,"Unterminated String.")
            return
        
        self.advance()
        value = self.source[self.start+1:self.current-1]
        self.addToken(T.STRING,value)

    def match(self,expected):
        print('match')
        if self.isAtEnd() :
            return False
        if self.source[self.current] != expected:
            return False
        
        self.current+=1
        return True
    
    def isAtEnd(self):
        print('isAtEnd')
        return self.current >= len(self.source)
    
    def peek(self):
        print('peek')
        if self.isAtEnd():
            return '\0'
        return self.source[self.current]
    
    def peekNext(self):
        print('peekNext')
        if self.current+1 >= len(self.source):
            return '\0'
        return self.source[self.current+1]
    
    def isAlpha(self,c):
        print('isAlpha')
        return (c>='a' and c<='z') or (c>= 'A' and c<= 'Z') or c=='_'

    def isAlphaNumeric(self,c):
        print('isAlphaNumeric')
        return self.isAlpha(c) or self.isDigit(c)

    def isDigit(self,c):
        print('isDigit')
        return c>='0' and c<='9' 
    
    def advance(self):
        print('advance')
        self.current+=1
        return self.source[self.current-1]
        
    def addToken(self,type,literal=None):
        print('addToken')
        text = self.source[self.start:self.current]
        self.TOKENS.append(Token(type,text,literal,self.line))
    
    


        

      

