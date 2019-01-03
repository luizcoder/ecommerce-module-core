<?php

namespace Mundipagg\Core\Kernel\Aggregates;

use DateTime;
use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;
use Mundipagg\Core\Kernel\Exceptions\InvalidParamException;
use Mundipagg\Core\Kernel\ValueObjects\Id\ChargeId;
use Mundipagg\Core\Kernel\ValueObjects\TransactionStatus;
use Mundipagg\Core\Kernel\ValueObjects\TransactionType;

final class Transaction extends AbstractEntity
{
    /**
     *
     * @var TransactionType
     */
    private $transactionType;
    /**
     *
     * @var int 
     */
    private $amount;
    /**
     *
     * @var TransactionStatus 
     */
    private $status;
    /**
     *
     * @var \DateTime 
     */
    private $createdAt;
    /**
     *
     * @var ChargeId 
     */
    private $chargeId;

    /**
     *
     * @return TransactionType
     */
    public function getTransactionType()
    {
        return $this->transactionType;
    }

    /**
     *
     * @param TransactionType $transactionType
     */
    public function setTransactionType(TransactionType $transactionType)
    {
        $this->transactionType = $transactionType;
    }

    /**
     *
     * @return int
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     *
     * @param int $amount
     */
    public function setAmount(int $amount)
    {
        if ($amount < 0) {
            throw new InvalidParamException(
                'Amount should be greater than or equal to 0!',
                $amount
            );
        }

        $this->amount = $amount;
    }

    /**
     *
     * @return TransactionStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     *
     * @return DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     *
     * @param  DateTime $createdAt
     * @return Transaction
     */
    public function setCreatedAt(DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }


    /**
     *
     * @param TransactionStatus $status
     */
    public function setStatus(TransactionStatus $status)
    {
        $this->status = $status;
    }

    /**
     *
     * @return ChargeId
     */
    public function getChargeId()
    {
        return $this->chargeId;
    }

    /**
     *
     * @param  ChargeId $chargeId
     * @return Transaction
     */
    public function setChargeId(ChargeId $chargeId)
    {
        $this->chargeId = $chargeId;
        return $this;
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
        $obj = new \stdClass();

        $obj->id = $this->getId();
        $obj->mundipaggId = $this->getMundipaggId();
        $obj->chargeId = $this->getChargeId();
        $obj->amount = $this->getAmount();
        $obj->type = $this->getTransactionType();
        $obj->status = $this->getStatus();
        $obj->createdAt = $this->getCreatedAt()->format('Y-m-d H:i:s');

        return $obj;
    }
}