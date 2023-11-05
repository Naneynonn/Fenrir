<?php

declare(strict_types=1);

namespace Ragnarok\Fenrir\Mapping;

use ReflectionClass;
use ReflectionException;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionUnionType;
use Throwable;

class Mapper
{
    public function map(mixed $source, string $definition): CompletedMapping
    {
        if (is_object($source)) {
            return $this->mapFromObject($source, $definition);
        }

        try {
            $constructorArgs = is_array($source) ? $source : [$source];
            $instance = new $definition(...$constructorArgs);

            return new CompletedMapping($instance, []);
        } catch (Throwable $e) {
            return new CompletedMapping(null, [
                new MappingException('Unable to instantiate property', '', $definition, $e)
            ]);
        }
    }

    private function mapFromObject(mixed $source, string $definition): CompletedMapping
    {
        $reflection = new ReflectionClass($definition);
        $instance = new $definition();

        $errors = [];
        $data = get_object_vars($source);

        foreach ($data as $key => $value) {
            try {
                $this->setProperty(
                    $value,
                    $reflection->getProperty($key),
                    $instance,
                    $errors
                );
            } catch (ReflectionException $e) {
                $errors[] = new MappingException('Property does not exist on definition', $key, $definition, $e);
            }
        }

        return new CompletedMapping($instance, $errors);
    }

    private function setProperty(
        mixed $value,
        ReflectionProperty $reflectionProperty,
        mixed &$instance,
        array &$errors,
    ): void {
        $type = $reflectionProperty->getType();

        /**
         * Typing should match for Union Types & non-set types
         */
        if ($type instanceof ReflectionUnionType || is_null($type)) {
            $this->setFlat($reflectionProperty, $instance, $value, $errors);
            return;
        }

        /**
         * IntersecionType is not used
         * e.g. TypeA&TypeB
         */
        if ($type instanceof ReflectionIntersectionType) {
            $errors[] = new MappingException('Unsupported typing', $reflectionProperty->getName(), get_class($instance));
            return;
        }

        /**
         * Scalar types
         */
        if (($type instanceof ReflectionNamedType && $type->isBuiltin())) {
            $this->setNamedType($reflectionProperty, $type, $instance, $value, $errors);
            return;
        }

        $typeName = $type->getName();

        if (enum_exists($typeName)) {
            $this->setEnum($reflectionProperty, $instance, $value, $typeName, $errors);
            return;
        }

        if (class_exists($typeName)) {
            $this->setClass($reflectionProperty, $instance, $value, $typeName, $errors);
            return;
        }

        $errors[] = new MappingException('Unsupported typing', $reflectionProperty->getName(), get_class($instance));
    }

    private function setFlat(
        ReflectionProperty $reflectionProperty,
        mixed &$instance,
        mixed $value,
        array &$errors,
    ): void {
        try {
            $reflectionProperty->setValue($instance, $value);
        } catch (Throwable $e) {
            $errors[] = new MappingException($e->getMessage(), $reflectionProperty->getName(), get_class($instance), $e);
        }

        return;
    }

    private function setNamedType(
        ReflectionProperty $reflectionProperty,
        ReflectionNamedType $type,
        mixed &$instance,
        mixed $value,
        array &$errors,
    ): void {
        if ($type->getName() === 'array') {
            $this->setArray($reflectionProperty, $instance, $value, $errors);

            return;
        }

        try {
            $reflectionProperty->setValue($instance, $value);
        } catch (Throwable $e) {
            $errors[] = new MappingException($e->getMessage(), $reflectionProperty->getName(), get_class($instance), $e);
        }

        return;
    }

    private function setArray(
        ReflectionProperty $reflectionProperty,
        mixed &$instance,
        mixed $value,
        array &$errors,
    ): void {
        if (!is_array($value)) {
            $errors[] = new MappingException('Unable to map non-array to array', $reflectionProperty->getName(), get_class($instance));
            return;
        }

        $attributes = $reflectionProperty->getAttributes(ArrayMapping::class);

        /**
         * Only arrays with a custom type should use the attribute
         */
        $arrayValue = count($attributes) > 0
            ? $this->mapArray($value, array_pop($attributes)->newInstance(), $errors)
            : $value;

        try {
            $reflectionProperty->setValue($instance, $arrayValue);
        } catch (Throwable $e) {
            $errors[] = new MappingException($e->getMessage(), $reflectionProperty->getName(), get_class($instance), $e);
        }
    }

    private function setEnum(
        ReflectionProperty $reflectionProperty,
        mixed &$instance,
        mixed $value,
        string $enum,
        array &$errors,
    ): void {
        try {
            $reflectionProperty->setValue($instance, $enum::tryFrom($value));
        } catch (Throwable $e) {
            $errors[] = new MappingException($e->getMessage(), $reflectionProperty->getName(), get_class($instance), $e);
        }

        return;
    }

    private function setClass(
        ReflectionProperty $reflectionProperty,
        mixed &$instance,
        mixed $value,
        string $class,
        array &$errors,
    ): void {
        $mappedValue = $this->map($value, $class);

        $errors = [...$errors, ...$mappedValue->errors];

        try {
            $reflectionProperty->setValue($instance, $mappedValue->result);
        } catch (Throwable $e) {
            $errors[] = new MappingException($e->getMessage(), $reflectionProperty->getName(), get_class($instance), $e);
        }

        return;
    }

    private function mapArray(array $values, ArrayMapping $arrayMapping, array &$errors)
    {
        return enum_exists($arrayMapping->definition)
            ? $this->mapEnumArray($values, $arrayMapping, $errors)
            : $this->mapClassArray($values, $arrayMapping, $errors);
    }

    private function mapClassArray(array $values, ArrayMapping $arrayMapping, array &$errors)
    {
        $new = [];

        foreach ($values as $key => $value) {
            $completedMapping = $this->map($value, $arrayMapping->definition);

            $errors = [...$errors, ...$completedMapping->errors];
            $new[$key] = $completedMapping->result;
        }

        return $new;
    }

    private function mapEnumArray(array $values, ArrayMapping $arrayMapping, array &$errors)
    {
        $new = [];

        foreach ($values as $key => $value) {
            try {
                $new[$key] = $arrayMapping->definition::tryFrom($value);
            } catch (Throwable $e) {
                $errors[] = new MappingException($e->getMessage(), '', $arrayMapping->definition, $e);
                $new[$key] = null;
            }
        }

        return $new;
    }
}
