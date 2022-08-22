<?php

namespace Test\Rest\Classes;

use JsonSerializable;

class Pet implements JsonSerializable
{
    protected int $id;
    protected ?Category $category;
    protected string $name;
    /**
     * @var array<string>
     */
    protected array $photoUrls;
    /**
     * @var array<Tag>
     */
    protected array $tags;
    protected string $status;

    /**
     * Pet constructor.
     *
     * @param int                   $id
     * @param Category|null         $category
     * @param string                $name
     * @param array<string, string> $photoUrls
     * @param array<Tag>            $tags
     * @param string                $status
     */
    public function __construct(int $id, ?Category $category, string $name, array $photoUrls, array $tags, string $status)
    {
        $this->id = $id;
        $this->category = $category;
        $this->name = $name;
        $this->photoUrls = $photoUrls;
        $this->tags = $tags;
        $this->status = $status;
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
     * @return Pet
     */
    public function setId(int $id): Pet
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return Category|null
     */
    public function getCategory(): ?Category
    {
        return $this->category;
    }

    /**
     * @param Category|null $category
     *
     * @return Pet
     */
    public function setCategory(?Category $category): Pet
    {
        $this->category = $category;

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
     * @return Pet
     */
    public function setName(string $name): Pet
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return array<string, string>
     */
    public function getPhotoUrls(): array
    {
        return $this->photoUrls;
    }

    /**
     * @param array<string, string> $photoUrls
     *
     * @return Pet
     */
    public function setPhotoUrls(array $photoUrls): Pet
    {
        $this->photoUrls = $photoUrls;

        return $this;
    }

    /**
     * @return array<Tag>
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * @param array<Tag> $tags
     *
     * @return Pet
     */
    public function setTags(array $tags): Pet
    {
        $this->tags = $tags;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     *
     * @return Pet
     */
    public function setStatus(string $status): Pet
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'id'        => $this->getId(),
            'category'  => $this->getCategory(),
            'name'      => $this->getName(),
            'photoUrls' => $this->getPhotoUrls(),
            'tags'      => $this->getTags(),
            'status'    => $this->getStatus(),
        ];
    }
}
