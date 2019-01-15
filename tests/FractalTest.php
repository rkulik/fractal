<?php

namespace Rkulik\Fractal\Tests;

use Closure;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Scope;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Rkulik\Fractal\Fractal;

/**
 * Class FractalTest
 *
 * @package Rkulik\Fractal\Tests
 */
final class FractalTest extends TestCase
{
    /**
     * @var Fractal
     */
    private $fractal;

    /**
     * @var Manager
     */
    private $manager;

    public function setUp()
    {
        parent::setUp();

        /** @var Manager|MockObject $manager */
        $this->manager = $this->createMock(Manager::class);

        $this->fractal = new Fractal($this->manager);
    }

    public function testItemToArray()
    {
        $resource = $this->createMock(\stdClass::class);
        $expected = ['foo'];

        $this->manager->expects($this->once())
            ->method('createData')
            ->will($this->returnCallback($this->createCallback(Item::class, 'toArray', $resource, $expected)));

        $this->assertSame($expected, $this->fractal->item($resource, $this->createTransformer($resource))->toArray());
    }

    public function testItemToJson()
    {
        $resource = $this->createMock(\stdClass::class);
        $expected = json_encode(['foo']);

        $this->manager->expects($this->once())
            ->method('createData')
            ->will($this->returnCallback($this->createCallback(Item::class, 'toJson', $resource, $expected)));

        $this->assertSame($expected, $this->fractal->item($resource, $this->createTransformer($resource))->toJson());
    }

    public function testCollectionToArray()
    {
        $resources = [$this->createMock(\stdClass::class), $this->createMock(\stdClass::class)];
        $expected = ['foo', 'bar'];

        $this->manager->expects($this->once())
            ->method('createData')
            ->will($this->returnCallback($this->createCallback(Collection::class, 'toArray', $resources, $expected)));

        $this->assertSame(
            $expected,
            $this->fractal->collection($resources, $this->createTransformer($resources))->toArray()
        );
    }

    public function testCollectionToJson()
    {
        $resources = [$this->createMock(\stdClass::class), $this->createMock(\stdClass::class)];
        $expected = json_encode(['foo', 'bar']);

        $this->manager->expects($this->once())
            ->method('createData')
            ->will($this->returnCallback($this->createCallback(Collection::class, 'toJson', $resources, $expected)));

        $this->assertSame(
            $expected,
            $this->fractal->collection($resources, $this->createTransformer($resources))->toJson()
        );
    }

    /**
     * @param mixed $resourceData
     *
     * @return Closure
     */
    private function createTransformer($resourceData)
    {
        $that = $this;
        return function ($data) use ($that, $resourceData) {
            $that->assertSame($resourceData, $data);

            return [];
        };
    }

    /**
     * @param string $converterMethod
     * @param array|string $expected
     *
     * @return Scope|MockObject
     */
    private function createScope(string $converterMethod, $expected): Scope
    {
        $scope = $this->createMock(Scope::class);
        $scope->expects($this->once())->method($converterMethod)->will($this->returnValue($expected));

        return $scope;
    }

    /**
     * @param string $className
     * @param string $converterMethod
     * @param mixed $resourceData
     * @param $expected
     *
     * @return Closure
     */
    private function createCallback(string $className, string $converterMethod, $resourceData, $expected): Closure
    {
        $scope = $this->createScope($converterMethod, $expected);
        $that = $this;

        return function ($resource) use ($that, $className, $scope, $resourceData) {
            $that->assertInstanceOf($className, $resource);
            /** @var $resource Item */
            $this->assertSame($resourceData, $resource->getData());

            return $scope;
        };
    }

    // testItemParsesIncludes
    // testItemContainsMetaInfo
    // testCollectionPaginates
}
