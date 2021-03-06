<?php

class StringEqualTest extends PHPUnit_Framework_TestCase
{
    public function testEvaluate()
    {
        $func = new \Galmi\Xacml\Func\Equality\StringEqual();
        $bringType = self::getMethod('bringType');

        $this->assertInternalType('string', $bringType->invokeArgs($func, [1]));
    }

    protected function getMethod($name) {
        $class = new ReflectionClass('\Galmi\Xacml\Func\Equality\StringEqual');
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }
}
