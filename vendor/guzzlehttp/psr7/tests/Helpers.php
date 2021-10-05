<?php

namespace GuzzleHttp\Tests\Psr7;

class Helpers
{
    /**
     * @param object $object
     * @param string $attributeName
     */
    public static function readObjectAttribute($object, $attributeName)
    {
        $reflector = new \ReflectionObject($object);

        do {
            try {
                $attribute = $reflector->getProperty($attributeName);

                if (!$attribute || $attribute->isPublic()) {
                    return $object->$attributeName;
                }

                $attribute->setAccessible(true);

                return $attribute->getValue($object);
            } catch (\ReflectionException $e) {
                // do nothing
            }
        } while ($reflector = $reflector->getParentClass());

        throw new \Exception(
            sprintf('Attribute "%s" not found in object.', $attributeName)
        );
    }
}
