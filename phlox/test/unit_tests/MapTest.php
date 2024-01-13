<?php declare(strict_types = 1);

use PHPUnit\Framework\TestCase;
use Phlox\DS\Map;

final class MapTest extends TestCase
{
    public function testMapStringKeyHasCorrectValue():void
    {
        $string = 'This is a test string of n Length';
        $int = 8849;
        $map = new Map();
        $map->put($string, $int);

        $this->assertSame($int, $map->get($string));
    }

    public function testMapObjectKeyHasCorrectValue():void
    {
        $object = new ArrayObject([4,5,34,6]);
        $bool = false;
        $map = new Map();
        $map->put($object, $bool);

        $this->assertSame($bool, $map->get($object));
    }

    public function testMapFoundCorrectValueForKey():void
    {
        $object = new ArrayObject([4,5,34,6]);
        $item = [948,85858,44];
        $map = new Map();
        $map->put($object, $item);

        $this->assertTrue($map->hasKey($object));
    }

}
