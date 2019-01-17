<?php

namespace Rkulik\Fractal\Tests;

use Closure;
use League\Fractal\Manager;
use League\Fractal\Pagination\CursorInterface;
use League\Fractal\Pagination\PaginatorInterface;
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
 *
 * @author René Kulik <rene@kulik.io>
 */
final class FractalTest extends TestCase
{
    /**
     * @var Fractal
     */
    private $fractal;

    /**
     * @var Manager|MockObject
     */
    private $manager;

    /**
     *
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->manager = $this->createMock(Manager::class);

        $this->fractal = new Fractal($this->manager);
    }

    /**
     *
     */
    public function testItemToArray(): void
    {
        $resource = $this->createMock(\stdClass::class);
        $expected = ['foo'];

        $this->manager->expects($this->once())
            ->method('createData')
            ->will($this->returnCallback($this->createCallback(Item::class, 'toArray', $resource, $expected)));

        $this->assertSame($expected, $this->fractal->item($resource, $this->createTransformer($resource))->toArray());
    }

    /**
     *
     */
    public function testItemToJson(): void
    {
        $resource = $this->createMock(\stdClass::class);
        $expected = json_encode(['foo']);

        $this->manager->expects($this->once())
            ->method('createData')
            ->will($this->returnCallback($this->createCallback(Item::class, 'toJson', $resource, $expected)));

        $this->assertSame($expected, $this->fractal->item($resource, $this->createTransformer($resource))->toJson());
    }

    /**
     *
     */
    public function testCollectionToArray(): void
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

    /**
     *
     */
    public function testCollectionToJson(): void
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
     *
     */
    public function testItemParsesIncludes(): void
    {
        $resource = $this->createMock(\stdClass::class);
        $expected = ['foo'];

        $this->manager->expects($this->once())
            ->method('parseIncludes')
            ->with($this->equalTo('bar'));

        $this->manager->expects($this->once())
            ->method('createData')
            ->will($this->returnCallback($this->createCallback(Item::class, 'toArray', $resource, $expected)));

        $this->assertSame(
            $expected,
            $this->fractal->item($resource, $this->createTransformer($resource))->parseIncludes('bar')->toArray()
        );
    }

    /**
     *
     */
    public function testItemContainsMetaInfo(): void
    {
        $resource = $this->createMock(\stdClass::class);
        $expected = ['foo'];

        $this->manager->expects($this->once())
            ->method('createData')
            ->will($this->returnCallback($this->createCallback(Item::class, 'toArray', $resource, $expected)));

        $this->assertSame(
            $expected,
            $this->fractal->item($resource, $this->createTransformer($resource))->setMeta(['bar' => 'baz'])->toArray()
        );
    }

    /**
     *
     */
    public function testCollectionPaginatesUsingPaginator(): void
    {
        $resources = [$this->createMock(\stdClass::class), $this->createMock(\stdClass::class)];
        $expected = ['foo', 'bar'];

        /** @var PaginatorInterface|MockObject $paginator */
        $paginator = $this->createMock(PaginatorInterface::class);

        $this->manager->expects($this->once())
            ->method('createData')
            ->will($this->returnCallback($this->createCallback(Collection::class, 'toArray', $resources, $expected)));

        $this->assertSame(
            $expected,
            $this->fractal->collection($resources, $this->createTransformer($resources))
                ->setPaginator($paginator)
                ->toArray()
        );
    }

    /**
     *
     */
    public function testCollectionPaginatesUsingCursor(): void
    {
        $resources = [$this->createMock(\stdClass::class), $this->createMock(\stdClass::class)];
        $expected = ['foo', 'bar'];

        /** @var CursorInterface|MockObject $cursor */
        $cursor = $this->createMock(CursorInterface::class);

        $this->manager->expects($this->once())
            ->method('createData')
            ->will($this->returnCallback($this->createCallback(Collection::class, 'toArray', $resources, $expected)));

        $this->assertSame(
            $expected,
            $this->fractal->collection($resources, $this->createTransformer($resources))
                ->setCursor($cursor)
                ->toArray()
        );
    }

    /**
     * @param $resourceData
     *
     * @return Closure
     */
    private function createTransformer($resourceData): Closure
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
     * @return MockObject
     */
    private function createScope(string $converterMethod, $expected): MockObject
    {
        $scope = $this->createMock(Scope::class);
        $scope->expects($this->once())->method($converterMethod)->will($this->returnValue($expected));

        return $scope;
    }

    /**
     * @param string $className
     * @param string $converterMethod
     * @param mixed $resourceData
     * @param array|string $expected
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
}
