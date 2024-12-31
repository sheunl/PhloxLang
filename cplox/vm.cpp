#include "common.hpp"
#include "vm.hpp"

VM vm;

void initVM() {
}

void freeVM() {
}

InterpretResult interpret(Chunk& chunk) {
    vm.chunk = &chunk;
    vm.ip = vm.chunk.code.begin();
    return run();
}