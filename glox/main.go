// main.go: Entry point for the Glox VM application
package main

import (
  "fmt"
  "os"
  "phloxlang/glox/vm"
)

// main is the entry point for the Glox VM application
func main() {

  // Print command-line arguments
  fmt.Println(os.Args)
  // Initialize the VM
  vm.InitVM()
  // Ensure VM resources are freed on exit
  defer vm.FreeVM()
}