<?php

namespace Goodby\CQRS\FictionalDomain\Domain\Model;

/**
 * <<identity>>
 * <<value object>>
 */
class UserId
{
    /**
     * @var string
     */
    private $id;

    /**
     * @param string $id
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function id()
    {
        return $this->id;
    }
}
