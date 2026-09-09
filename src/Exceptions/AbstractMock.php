<?php

declare(strict_types=1);

namespace Chubbyphp\Mock\Exceptions;

use Chubbyphp\Mock\Utils;

abstract class AbstractMock extends \RuntimeException
{
    /**
     * @param array<string, bool> &$splObjectHashes
     */
    final protected function getData(mixed $value, array &$splObjectHashes = []): mixed
    {
        return match (true) {
            \is_array($value) => $this->getArrayData($value, $splObjectHashes),
            \is_object($value) => $this->getObjectData($value, $splObjectHashes),
            \is_resource($value) => '(resource)',
            default => $value,
        };
    }

    /**
     * @param array<mixed>        $value
     * @param array<string, bool> &$splObjectHashes
     *
     * @return array<mixed>
     */
    private function getArrayData(array $value, array &$splObjectHashes): array
    {
        $data = [];
        foreach ($value as $subKey => $subValue) {
            $data[$subKey] = $this->getData($subValue, $splObjectHashes);
        }

        return $data;
    }

    /**
     * @param array<string, bool> &$splObjectHashes
     *
     * @return array<mixed>|string
     */
    private function getObjectData(object $value, array &$splObjectHashes): array|string
    {
        $splObjectHash = spl_object_hash($value);

        if (isset($splObjectHashes[$splObjectHash])) {
            return '(recursion)';
        }

        $splObjectHashes[$splObjectHash] = true;

        if ($value instanceof \Throwable) {
            return $this->getThrowableData($value);
        }

        $reflectionObject = new \ReflectionObject($value);

        $data = $this->getSerializedObjectData($reflectionObject, $value, $splObjectHashes)
            ?? $this->getPropertiesObjectData($reflectionObject, $value, $splObjectHashes);

        $data['__CLASS__'] = $value::class;

        return $data;
    }

    /**
     * @param array<string, bool> &$splObjectHashes
     *
     * @return null|array<mixed>
     */
    private function getSerializedObjectData(
        \ReflectionObject $reflectionObject,
        object $value,
        array &$splObjectHashes
    ): ?array {
        foreach (['__serialize', '__sleep'] as $method) {
            if ($reflectionObject->hasMethod($method)) {
                $reflectionMethod = $reflectionObject->getMethod($method);

                /** @var array<mixed> */
                return $this->getData($reflectionMethod->invoke($value), $splObjectHashes);
            }
        }

        return null;
    }

    /**
     * @param array<string, bool> &$splObjectHashes
     *
     * @return array<mixed>
     */
    private function getPropertiesObjectData(
        \ReflectionObject $reflectionObject,
        object $value,
        array &$splObjectHashes
    ): array {
        $data = [];
        foreach ($reflectionObject->getProperties() as $reflectionProperty) {
            $subKey = $reflectionProperty->getName();
            $subValue = Utils::getPropertyValue($reflectionProperty, $value);

            $data[$subKey] = $this->getData($subValue, $splObjectHashes);
        }

        return $data;
    }

    /**
     * @return array<mixed>
     */
    private function getThrowableData(\Throwable $value): array
    {
        return [
            'message' => $value->getMessage(),
            'code' => $value->getCode(),
            'file' => Utils::replaceProjectInPath($value->getFile()),
            'line' => $value->getLine(),
            'previous' => $value->getPrevious() ? $this->getThrowableData($value->getPrevious()) : null,
            '__CLASS__' => $value::class,
        ];
    }
}
