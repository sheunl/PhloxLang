package debug

// int simpleInstruction(const std::string& name, int offset) {
//     std::cout << name << std::endl;
//     return offset + 1;
// }

// int constantInstruction(const std::string& name, Chunk& chunk, int offset) {
//     std::uint8_t constant = chunk.code[offset + 1];
//     std::cout << std::setfill('0') << std::setw(4) << name << " " << constant << " " << offset << " '";
//     printValue(chunk.constants[offset]);
//     std::cout << "'" << std::endl;
//     return offset + 2;
// }

// void disassembleChunk(Chunk& chunk,  std::string name) {
//     std::cout << "== " << name << " ==" << std::endl;
//     for (long unsigned int offset = 0; offset < chunk.code.size();) {
//         offset = disassembleInstruction(chunk, offset);
//     }
// }

// int disassembleInstruction(Chunk& chunk, int offset) {
//     std::cout << std::setfill('0') << std::setw(4) << offset << " ";
//     if (offset > 0 && chunk.lines[offset] == chunk.lines[offset - 1]) {
//         std::cout << "   | ";
//     } else {
//         std::cout << std::setfill('0') << std::setw(4) << chunk.lines[offset] << " ";
//     }

//     std::uint8_t instruction = chunk.code[offset];
//     switch (instruction) {
//         case OP_CONSTANT:
//          return constantInstruction("OP_CONSTANT", chunk, offset);
//         case OP_RETURN:
//             return simpleInstruction("OP_RETURN", offset);
//             break;
//         default:
//             std::cout << "Unknown opcode " << instruction << std::endl;
//             break;
//     }
//     return offset + 1;
// }

func SimpleInstruction(name string, offset int) int {
	fmt.Println(name)
	return offset + 1
}

func ConstantInstruction(name string, chunk *Chunk, offset int) int {
	constant := chunk.code[offset+1]
	fmt.Printf("%04d %s %d '%v'\n", offset, name, constant, chunk.constants[constant])
	return offset + 2
}

func DisassembleChunk(chunk *Chunk, name string) {
	fmt.Printf("== %s ==\n", name)
	for offset := 0; offset < len(chunk.code); {
		offset = DisassembleInstruction(chunk, offset)
	}
}

func DisassembleInstruction(chunk *Chunk, offset int) int {
	fmt.Printf("%04d ", offset)
	if offset > 0 && chunk.lines[offset] == chunk.lines[offset-1] {
		fmt.Print("   | ")
	} else {
		fmt.Printf("%04d ", chunk.lines[offset])
	}

	instruction := chunk.code[offset]
	switch instruction {
	case OP_CONSTANT:
		return ConstantInstruction("OP_CONSTANT", chunk, offset)
	case OP_RETURN:
		return SimpleInstruction("OP_RETURN", offset)
	default:
		fmt.Printf("Unknown opcode %d\n", instruction)
	}
	return offset + 1
}
