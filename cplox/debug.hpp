#ifndef cplox_debug_hpp
#define cplox_debug_hpp

#include <string>
#include <iostream>
#include "chunk.hpp"

void disassembleChunk(Chunk &chunk, std::string name);
int disassembleInstruction(Chunk &chunk, int offset);

#endif