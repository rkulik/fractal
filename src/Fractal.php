<?php

namespace Rkulik\Fractal;

use League\Fractal\Manager;
use League\Fractal\Pagination\CursorInterface;
use League\Fractal\Pagination\PaginatorInterface;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\ResourceAbstract;
use League\Fractal\Scope;
use League\Fractal\TransformerAbstract;

/**
 * Class Fractal
 *
 * @package Rkulik\Fractal
 *
 * @author René Kulik <rene@kulik.io>
 */
class Fractal
{
    /**
     * @var Manager
     */
    private $manager;

    /**
     * @var ResourceAbstract
     */
    private $resource;

    /**
     * @var array|string
     */
    private $includes;

    /**
     * @var PaginatorInterface
     */
    private $paginator;

    /**
     * @var CursorInterface
     */
    private $cursor;

    /**
     * Fractal constructor.
     *
     * @param Manager $manager
     */
    public function __construct(Manager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param mixed $data
     * @param callable|TransformerAbstract|null $transformer
     * @param string|null $resourceKey
     *
     * @return Fractal
     */
    public function item($data = null, $transformer = null, string $resourceKey = null): Fractal
    {
        $this->resource = new Item($data, $transformer, $resourceKey);

        return $this;
    }

    /**
     * @param mixed $data
     * @param callable|TransformerAbstract|null $transformer
     * @param string|null $resourceKey
     *
     * @return Fractal
     */
    public function collection($data = null, $transformer = null, string $resourceKey = null): Fractal
    {
        $this->resource = new Collection($data, $transformer, $resourceKey);

        return $this;
    }

    /**
     * @param array|string $includes
     *
     * @return Fractal
     */
    public function parseIncludes($includes): Fractal
    {
        $this->includes = $includes;

        return $this;
    }

    /**
     * @param PaginatorInterface $paginator
     *
     * @return Fractal
     */
    public function setPaginator(PaginatorInterface $paginator): Fractal
    {
        $this->paginator = $paginator;

        return $this;
    }

    /**
     * @param CursorInterface $cursor
     *
     * @return Fractal
     */
    public function setCursor(CursorInterface $cursor): Fractal
    {
        $this->cursor = $cursor;

        return $this;
    }

    /**
     * @param array $meta
     *
     * @return Fractal
     */
    public function setMeta(array $meta): Fractal
    {
        if ($this->resource) {
            $this->resource->setMeta($meta);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return $this->createData()->toArray();
    }

    /**
     * @param int $options
     *
     * @return string
     */
    public function toJson(int $options = 0): string
    {
        return $this->createData()->toJson($options);
    }

    /**
     * @return Scope
     */
    private function createData(): Scope
    {
        if ($this->includes) {
            $this->manager->parseIncludes($this->includes);
        }

        if ($this->resource instanceof Collection && $this->cursor) {
            $this->resource->setCursor($this->cursor);
        }

        if ($this->resource instanceof Collection && $this->paginator) {
            $this->resource->setPaginator($this->paginator);
        }

        return $this->manager->createData($this->resource);
    }
}
