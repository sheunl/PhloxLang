#include <iostream>
#include <string>

#include "common.hpp"
#include "chunk.hpp"
#include "vm.hpp"
#include "debug.hpp"


int main(int argc, const char* argv[]) {
    initVM();
    Chunk chunk;
    enum OpCode op = OP_RETURN;
    // enum OpCode op2 = OP_CONSTANT;
    enum OpCode op3 = OP_CONSTANT;

    int constant = addConstant(chunk, 1.2);

    writeChunk(chunk, op3, 123);

    writeChunk(chunk, constant, 123);

    writeChunk(chunk, op, 123);
    
    
    disassembleChunk(chunk, "test chunk");
    interpret(chunk);
    
    freeVM();
    chunk.code.clear();

    return 0;
}