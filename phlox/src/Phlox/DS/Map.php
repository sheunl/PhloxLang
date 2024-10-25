<?php

namespace Phlox\DS;

/**
 * Custom Map Data structure. 
 */
class Map
{
    private array $item_left = [];
    private array $item_right = [];

    /**
     */
    public function __construct (){

    }

    /**
     * Add the items into the Map data structure
     * 
     * @param array $item_l
     * @param array $item_r
     * 
     * @return void
     */
    public function put(array $item_l,array $item_r): void
    {
     $generatedKey = $this->generateKey($item_l);
     $this->item_left[$generatedKey] = $item_l;
     $this->item_right[$generatedKey] = $item_r;  
    }

    /**
     * Get the items from the Map data structure
     * 
     * @param array $item_l
     * 
     * @return mixed
     */
    public function get(array $item_l): mixed
    {
        $generatedKey = $this->generateKey($item_l);

        if(isset($this->item_right[$generatedKey])){
            return $this->item_right[$generatedKey];
        } else{
            return null;
        }
        
    }

    /**
     * Convert item in hashkey
     * 
     * @param mixed $item
     * 
     * @return mixed
     */
    private function generateKey(mixed $item): mixed
    {
        if(gettype($item) === 'object'){
            return spl_object_hash($item);
        }
        
        return Hash('sha256', $item);
    }

    /**
     * Check if item exist
     * 
     * @param mixed $key
     * 
     * @return bool
     */
    public function hasKey(mixed $key): bool
    {
        return in_array($this->generateKey($key), array_keys($this->item_left));
    }

}