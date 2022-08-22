<?php

namespace Test\Rest\Classes;

use JsonSerializable;

class Tag implements JsonSerializable
{
    protected int $id;
    protected string $name;

    /**
     * Category constructor.
     *
     * @param int    $id
     * @param string $name
     */
    public function __construct(int $id, string $name)
    {
        $this->id = $id;
        $this->name = $name;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return Tag
     */
    public function setId(int $id): Tag
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return Tag
     */
    public function setName(string $name): Tag
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'id'   => $this->getId(),
            'name' => $this->getName(),
        ];
    }
}
