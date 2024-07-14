mod chunk;
mod memory;

fn main() {
    
    use crate::chunk::OpCode::*;

    let mut v = Vec::new();
    v.push(OP_RETURN);
    disassembleChunk("Test Chunk");
    v.clear();
    assert!(matches!(v[..],[]));
}
