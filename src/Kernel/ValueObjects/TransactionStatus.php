<?php

namespace Mundipagg\Core\Kernel\ValueObjects;

use Mundipagg\Core\Kernel\Abstractions\AbstractValueObject;

final class TransactionStatus extends AbstractValueObject
{
    const CAPTURED = "captured";
    const PARTIAL_CAPTURE = "partial_capture";
    const AUTHORIZED_PENDING_CAPTURE = 'authorized_pending_capture';
    const VOIDED = 'voided';
    const PARTIAL_VOID = 'partial_void';

    /**
     *
     * @var string 
     */
    private $status;

    /**
     * OrderStatus constructor.
     *
     * @param string $status
     */
    private function __construct($status)
    {
        $this->setStatus($status);
    }

    public static function partialCapture()
    {
        return new self(self::PARTIAL_CAPTURE);
    }

    public static function captured()
    {
        return new self(self::CAPTURED);
    }

    public static function authorizedPendingCapture()
    {
        return new self(self::AUTHORIZED_PENDING_CAPTURE);
    }

    public static function voided()
    {
        return new self(self::VOIDED);
    }

    public static function partialVoid()
    {
        return new self(self::PARTIAL_VOID);
    }

    /**
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     *
     * @param  string $status
     * @return OrderStatus
     */
    private function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * To check the structural equality of value objects,
     * this method should be implemented in this class children.
     *
     * @param  OrderStatus $object
     * @return bool
     */
    protected function isEqual($object)
    {
        return $this->getStatus() === $object->getStatus();
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @link   https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since  5.4.0
     */
    public function jsonSerialize()
    {
        return $this->status;
    }
}