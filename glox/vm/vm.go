// vm.go: Virtual Machine core logic for Glox
package vm

import (
	"fmt"
	"phloxlang/glox/chunk"
	"phloxlang/glox/value"
)

type InterpretResult int

var vm struct {
	chunk  chunk.Chunk
	ip     []byte
}


const (
	INTERPRET_OK InterpretResult = iota
	INTERPRET_COMPILE_ERROR
	INTERPRET_RUNTIME_ERROR
)

// InitVM initializes the virtual machine and returns status code
func InitVM() int {
	fmt.Println("Initializing VM...")
	return 1
}

// FreeVM releases resources used by the virtual machine and returns status code
func FreeVM() int {
	fmt.Println("Freeing VM...")
	return 0
}

func run() InterpretResult {
	readByte := func() byte {
		b := vm.ip[0]
		vm.ip = vm.ip[1:]
		return b
	}

	readConstant := func() value.Value {
		return vm.chunk.constants[readByte()]
	}

	for {
        var instruction byte
        instruction = readByte()
        switch instruction {
        case chunk.OP_CONSTANT:
            constant := readConstant()
            value.PrintValue(constant)
            fmt.Println()
        case chunk.OP_RETURN:
            return INTERPRET_OK
        }
    }

}

func interpret(chunk *Chunk) InterpretResult {
	vm.chunk = chunk
	vm.ip = chunk.code
	return run()
}