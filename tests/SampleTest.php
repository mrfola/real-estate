<?php
use \PHPUnit\Framework\TestCase;

class SampleTest extends TestCase
{
    public function test_TrueAssertsToTrue(Type $var = null)
    {
        $this->assertTrue(true);
    }
}