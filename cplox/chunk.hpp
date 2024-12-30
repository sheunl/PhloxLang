#ifndef cplox_chunk_hpp
#define cplox_chunk_hpp

#include "common.hpp"
#include "value.hpp" 

enum OpCode {
    OP_CONSTANT,    // Represents an instruction to load a constant value
    OP_RETURN       // Represents a return instruction that exits the current function
};

// Represents a chunk of bytecode, containing the bytecode itself,
// constant values used by the chunk, and line number information
// for debugging purposes
struct Chunk {
    std::vector<std::uint8_t> code;      // The bytecode instructions
    std::vector<Value> constants;         // Pool of constant values
    std::vector<int> lines;              // Line numbers for each instruction
};

// Adds a constant value to the chunk's constant pool and returns its index
int addConstant(Chunk& chunk, Value value);

// Writes a byte of bytecode to the chunk and records its source line number
void writeChunk(Chunk& chunk, std::uint8_t byte, int line);

#endif
