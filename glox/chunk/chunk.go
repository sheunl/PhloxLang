
package chunk

import "phloxlang/glox/value"

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
