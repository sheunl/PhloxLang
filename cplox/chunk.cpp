#include "chunk.hpp"

// Adds a constant value to the chunk's constant pool and returns its index
int addConstant(Chunk& chunk, Value value) {
    chunk.constants.push_back(value);
    return chunk.constants.size() - 1;
}

// Writes a byte of bytecode to the chunk and records its source line number
void writeChunk(Chunk& chunk, std::uint8_t byte, int line) {
    chunk.code.push_back(byte);
    chunk.lines.push_back(line);
}