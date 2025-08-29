// vm.go: Virtual Machine core logic for Glox
package vm

import "fmt"

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