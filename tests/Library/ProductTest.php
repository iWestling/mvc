<?php

namespace App\Tests\Entity;

use App\Entity\Product;
use PHPUnit\Framework\TestCase;

class ProductTest extends TestCase
{
    public function testSetName(): void
    {
        $product = new Product();
        $product->setName('Sample Product');
        $this->assertSame('Sample Product', $product->getName());
    }

    public function testSetValue(): void
    {
        $product = new Product();
        $product->setValue(123);
        $this->assertSame(123, $product->getValue());
    }
}
