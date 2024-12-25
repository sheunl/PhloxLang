<?php

namespace Phlox\DS;

/**
 * Map class provides a key-value pair data structure
 * where both keys and values can be of any type.
 * It uses hashing to generate unique keys for objects and scalar values.
 */
class Map
{
    private array $item_left = [];
    private array $item_right = [];

    /**
     * Initializes an empty Map instance
     */
    public function __construct (){ }

    /**
     * Stores a key-value pair in the map
     * @param mixed $item_l The key to store
     * @param mixed $item_r The value to associate with the key
     * @return void
     */
    public function put($item_l, $item_r)
    {
     $generatedKey = $this->generateKey($item_l);
     $this->item_left[$generatedKey] = $item_l;
     $this->item_right[$generatedKey] = $item_r;  
    }

    /**
     * Retrieves the value associated with a given key
     * @param mixed $item_l The key to look up
     * @return mixed|null The value associated with the key, or null if key not found
     */
    public function get($item_l)
    {
        $generatedKey = $this->generateKey($item_l);

        if(isset($this->item_right[$generatedKey])){
            return $this->item_right[$generatedKey];
        } else{
            return null;
        }
        
    }

    /**
     * Generates a unique hash key for a given item
     * Uses spl_object_hash for objects and sha256 for other types
     * @param mixed $item The item to generate a key for
     * @return string The generated hash key
     */
    private function generateKey($item)
    {
        if(gettype($item) === 'object'){
            return spl_object_hash($item);
        }
        
        return hash('sha256', $item);
    }

    /**
     * Checks if a key exists in the map
     * @param mixed $key The key to check
     * @return bool True if the key exists, false otherwise
     */
    public function hasKey($key): bool
    {
        return in_array($this->generateKey($key), array_keys($this->item_left));
    }

}