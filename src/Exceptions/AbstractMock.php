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
        if (\is_array($value)) {
            return $this->getArrayData($value, $splObjectHashes);
        }

        if (\is_object($value)) {
            return $this->getObjectData($value, $splObjectHashes);
        }

        if (\is_resource($value)) {
            return '(resource)';
        }

        return $value;
    }

    /**
     * @param array<mixed>        $value
     * @param array<string, bool> &$splObjectHashes
     *
     * @return array<string>
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

        foreach (['__serialize', '__sleep'] as $method) {
            if ($reflectionObject->hasMethod($method)) {
                $reflectionMethod = $reflectionObject->getMethod($method);

                /** @var array<mixed> */
                $data = $this->getData($reflectionMethod->invoke($value), $splObjectHashes);

                $data['__CLASS__'] = $value::class;

                return $data;
            }
        }

        $data = [];
        foreach ($reflectionObject->getProperties() as $reflectionProperty) {
            $subKey = $reflectionProperty->getName();
            $subValue = $reflectionProperty->isInitialized($value)
                ? $reflectionProperty->getValue($value) : '(uninitialized)';

            $data[$subKey] = $this->getData($subValue, $splObjectHashes);
        }

        $data['__CLASS__'] = $value::class;

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
