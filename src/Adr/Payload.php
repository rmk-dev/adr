<?php

namespace Rmk\Adr;

/**
 * Class Payload
 *
 * @package Rmk\Adr
 */
class Payload
{

    public const OK = 0;

    public const NOT_FOUND = 1;

    public const INTERNAL_ERR = 2;

    public const ERR = -1;

    protected int $status;

    protected $data;

    /**
     * Payload constructor.
     * @param int $status
     * @param $data
     */
    public function __construct(int $status, $data)
    {
        $this->setStatus($status);
        $this->setData($data);
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @param int $status
     * @return Payload
     */
    public function setStatus(int $status): Payload
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     * @return Payload
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    public function isError(): bool
    {
        return (bool) $this->status;
    }

    public function isNotFoundError(): bool
    {
        return $this->getStatus() === self::NOT_FOUND;
    }

    public function isInternalError(): bool
    {
        return $this->getStatus() === self::INTERNAL_ERR;
    }

    public function isOk(): bool
    {
        return !$this->isError();
    }
}
