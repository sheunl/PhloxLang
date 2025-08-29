
package vm

import "phloxlang/glox/value"

// int addConstant(Chunk& chunk, Value value) {
//     chunk.constants.push_back(value);
//     return chunk.constants.size() - 1;
// }

// // Writes a byte of bytecode to the chunk and records its source line number
// void writeChunk(Chunk& chunk, std::uint8_t byte, int line) {
//     chunk.code.push_back(byte);
//     chunk.lines.push_back(line);
// }

const (
	OP_CONSTANT = iota // Represents an instruction to load a constant value
	OP_RETURN          // Represents a return instruction that exits the current function
)

type Chunk struct {
	code     []byte
	lines    []int
	constants []value.Value
}

func AddConstant(chunk *Chunk, value value.Value) int {
	chunk.constants = append(chunk.constants, value)
	return len(chunk.constants) - 1
}

func WriteChunk(chunk *Chunk, byte byte, line int) {
	chunk.code = append(chunk.code, byte)
	chunk.lines = append(chunk.lines, line)
}
