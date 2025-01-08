#include "common.hpp"
#include "vm.hpp"
#include <iostream>

VM vm;

void initVM() {
}

void freeVM() {
}

static InterpretResult run() {
    #define READ_BYTE() (*vm.ip++)
    #define READ_CONSTANT() (vm.chunk.constants[READ_BYTE()])

    for (;;) {
        uint8_t instruction;
        switch (instruction = READ_BYTE()) {
            case OP_CONSTANT: {
                Value constant = READ_CONSTANT();
                printValue(constant);
                std::cout << std::endl;
                break;
            }
            case OP_RETURN:
                return INTERPRET_OK;
        }
    }

    #undef READ_BYTE
    #undef READ_CONSTANT
}

InterpretResult interpret(Chunk& chunk) {
    vm.chunk = chunk;
    vm.ip = vm.chunk.code.data();
    return run();
}