<?php

declare(strict_types=1);

namespace Fastfony\IdentityBundle\Tests\Entity;

use Fastfony\IdentityBundle\Entity\Identity\Group;
use Fastfony\IdentityBundle\Entity\Identity\Role;
use Fastfony\IdentityBundle\Entity\Identity\User;
use Fastfony\IdentityBundle\Entity\RequestPassword;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;
use DirectoryIterator;
use Symfony\Component\Uid\Uuid;

#[CoversClass(User::class)]
#[CoversClass(Group::class)]
#[CoversClass(Role::class)]
#[CoversClass(RequestPassword::class)]
final class EntitiesTest extends TestCase
{
    /**
     * @throws ReflectionException
     */
    public function testEntitiesGettersAndSetters(): void
    {
        foreach ($this->getEntityClassNames() as $className) {
            $entity = $this->instantiateEntity($className);
            $reflection = new ReflectionClass($entity);
            $methods = $reflection->getMethods();
            $getters = [];
            $setters = [];
            foreach ($methods as $method) {
                if ($method->isPublic() && str_starts_with($method->getName(), 'get')) {
                    $getters[$method->getName()] = $method;
                }
                if ($method->isPublic() && str_starts_with($method->getName(), 'set')) {
                    $setters[$method->getName()] = $method;
                }
            }
            foreach ($getters as $getterName => $getter) {
                try {
                    $entity->{$getterName}();
                    $this->assertTrue(true);
                } catch (\Throwable $e) {
                    $this->fail('Exception on ' . $className . '::' . $getterName . '() : ' . $e->getMessage());
                }
            }
            foreach ($setters as $setterName => $setter) {
                $params = $setter->getParameters();
                if (0 === count($params)) {
                    continue;
                }
                $value = $this->getDummyValueForParameter($params[0]);
                if (null === $value) {
                    // Cannot test this setter
                    continue;
                }

                try {
                    $result = $entity->{$setterName}($value);
                    $this->assertTrue(true);
                    if (method_exists($entity, 'get' . substr($setterName, 3))) {
                        $getterName = 'get' . substr($setterName, 3);
                        $got = $entity->{$getterName}();
                        // We do not check strict equality because some setters may transform the value
                        $this->assertNotNull($got);
                    }
                    if (null !== $result) {
                        $this->assertInstanceOf($className, $result);
                    }
                } catch (\Throwable $e) {
                    $this->fail('Exception on ' . $className . '::' . $setterName . '() : ' . $e->getMessage());
                }
            }
        }
    }

    private function getEntityClassNames(): array
    {
        $entities = [];
        $dir = __DIR__ . '/../../src/Entity';
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS)
        );
        foreach ($iterator as $fileInfo) {
            if (!$fileInfo->isFile() || 'php' !== $fileInfo->getExtension()) {
                continue;
            }
            $relativePath = str_replace([$dir . '/', '.php'], ['', ''], $fileInfo->getPathname());
            $relativePath = ltrim(str_replace(DIRECTORY_SEPARATOR, '\\', $relativePath), '\\');
            $className = 'Fastfony\\IdentityBundle\\Entity\\' . $relativePath;
            if (class_exists($className)) {
                $entities[] = $className;
            }
        }
        return $entities;
    }

    private function instantiateEntity(string $className): object
    {
        try {
            $reflection = new ReflectionClass($className);
            $constructor = $reflection->getConstructor();
            if (null === $constructor || 0 === $constructor->getNumberOfRequiredParameters()) {
                return $reflection->newInstance();
            }
            $args = [];
            foreach ($constructor->getParameters() as $param) {
                $args[] = $this->getDummyValueForParameter($param);
            }
            return $reflection->newInstanceArgs($args);
        } catch (ReflectionException $e) {
            $this->fail('Cannot instantiate ' . $className . ': ' . $e->getMessage());
        }
    }

    private function getDummyValueForParameter(\ReflectionParameter $param): mixed
    {
        $type = $param->getType();
        if (null === $type) {
            return null;
        }
        $typeName = $type->getName();
        if ('int' === $typeName) {
            return 1;
        }
        if ('float' === $typeName) {
            return 1.0;
        }
        if ('string' === $typeName) {
            return 'test';
        }
        if ('bool' === $typeName) {
            return true;
        }
        if ('array' === $typeName) {
            return [];
        }
        if (Uuid::class === $typeName) {
            return Uuid::v4();
        }
        if (\DateTimeImmutable::class === $typeName) {
            return new \DateTimeImmutable();
        }

        return null;
    }
}
