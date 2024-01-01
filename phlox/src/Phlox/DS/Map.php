<?php

namespace Phlox\DS;

class Map
{
    private array $item_left = [];
    private array $item_right = [];

    public function __construct (){

    }

    public function put($item_l, $item_r)
    {
     $generatedKey = $this->generateKey($item_l);
     $this->item_left[$generatedKey] = $item_l;
     $this->item_right[$generatedKey] = $item_r;  
    }

    public function get($item_l)
    {
        $generatedKey = $this->generateKey($item_l);

        if(isset($this->item_right[$generatedKey])){
            return $this->item_right[$generatedKey];
        } else{
            return null;
        }
        
    }

    private function generateKey($item)
    {
        return Hash('sha256', print_r($item, true));
    }

    public function hasKey($key): bool
    {
        return in_array($this->generateKey($key), array_keys($this->item_left));
    }

}