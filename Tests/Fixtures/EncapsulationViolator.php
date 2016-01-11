<?php

namespace Afrihost\BaseCommandBundle\Tests\Fixtures;


class EncapsulationViolator
{
    /**
     * Call protected/private method of a class. A method that violates the encapsulation is needed because the primary
     * user of the bundle is a developer who overrides the classes (and thus makes use of protected methods which should
     *  be tested )
     *
     * @param object &$object    Instantiated object that we will run method on.
     * @param string $methodName Method name to call
     * @param array  $parameters Array of parameters to pass into method.
     *
     * @return mixed Method return.
     */
    public static function invokeMethod(&$object, $methodName, array $parameters = array())
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);

        $method->setAccessible(true);
        $result = $method->invokeArgs($object, $parameters);
        $method->setAccessible(false);

        return $result;
    }

}