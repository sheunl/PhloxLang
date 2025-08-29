package value

import "fmt"

type Value float64 // Define Value as a float64 for simplicity

type ValueArray []Value

func PrintValue(value Value) {
    fmt.Print(value)
}