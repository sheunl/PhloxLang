#ifndef cplox_vm_hpp
#define cplox_vm_hpp

#include "chunk.hpp"

struct VM {
    Chunk chunk;
    std::vector<uint8_t> ip;
};

enum InterpretResult {
    INTERPRET_OK,
    INTERPRET_COMPILE_ERROR,
    INTERPRET_RUNTIME_ERROR
};

void initVM();
void freeVM();
InterpretResult interpret(Chunk& chunk);

#endif