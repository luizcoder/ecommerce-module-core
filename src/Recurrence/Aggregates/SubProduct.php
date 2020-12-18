<?php

namespace Mundipagg\Core\Recurrence\Aggregates;

use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;
use Mundipagg\Core\Recurrence\Interfaces\SubProductEntityInterface;
use Mundipagg\Core\Recurrence\ValueObjects\PricingSchemeValueObject;
use Mundipagg\Core\Recurrence\Aggregates\Repetition;

class SubProduct extends AbstractEntity implements SubProductEntityInterface
{
    const DATE_FORMAT = 'Y-m-d H:i:s';

    /** @var int */
    protected $id;
    /** @var int */
    protected $productId;
    /** @var int */
    protected $productRecurrenceId;
    /** @var string */
    protected $recurrenceType;
    /** @var string */
    protected $name;
    /** @var string */
    protected $description;
    /** @var PricingSchemeValueObject */
    protected $pricingScheme;
    /** @var int */
    protected $quantity;
    /** @var int */
    protected $cycles;
    /** @var string */
    protected $createdAt;
    /** @var string */
    protected $updatedAt;

    protected $increment;

    /** @var \Mundipagg\Core\Recurrence\Aggregates\Repetition */
    protected $selectedRepetition;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return SubProduct
     */
    public function setId($id)
    {
        $this->id = intval($id);
        return $this;
    }

    /**
     * @return int
     */
    public function getProductRecurrenceId()
    {
        return $this->productRecurrenceId;
    }

    /**
     * @param int $productRecurrenceId
     * @return SubProduct
     */
    public function setProductRecurrenceId($productRecurrenceId)
    {
        $this->productRecurrenceId = intval($productRecurrenceId);
        return $this;
    }

    /**
     * @return int
     */
    public function getProductId()
    {
        return $this->productId;
    }

    /**
     * @param int $productId
     * @return SubProduct
     */
    public function setProductId($productId)
    {
        $this->productId = intval($productId);
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return Template
     * @throws \Exception
     */
    public function setDescription($description)
    {
        $description = substr(strip_tags($description), 0, 256);

        $this->description = $description;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Template
     * @throws \Exception
     */
    public function setName($name)
    {
        if (preg_match('/[^a-zA-Z0-9 ]+/i', $name)) {
            $name = preg_replace('/[^a-zA-Z0-9 ]+/i', '', $name);
        }

        $this->name = substr($name, 0, 256);
        return $this;
    }

    /**
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param int $quantity
     * @return SubProduct
     */
    public function setQuantity($quantity)
    {
        $this->quantity = intval($quantity);
        return $this;
    }

    /**
     * @return int
     */
    public function getCycles()
    {
        return $this->cycles;
    }

    /**
     * @param int $cycles
     * @return SubProduct
     */
    public function setCycles($cycles)
    {
        $this->cycles = intval($cycles);
        return $this;
    }

    /**
     * @return Increment
     */
    public function getIncrement()
    {
        return $this->increment;
    }

    /**
     * @param Increment $increment
     * @return SubProduct
     */
    public function setIncrement(Increment $increment)
    {
        $this->increment = $increment;
        return $this;
    }

    /**
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     * @return SubProduct
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt->format(self::DATE_FORMAT);
        return $this;
    }

    /**
     * @return string
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updatedAt
     * @return SubProduct
     */
    public function setUpdatedAt(\DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt->format(self::DATE_FORMAT);
        return $this;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return [
            "id" => $this->getId(),
            "productRecurrenceId" => $this->getProductRecurrenceId(),
            "productId" => $this->getProductId(),
            "recurrenceType" => $this->getRecurrenceType(),
            "name" => $this->getName(),
            "description" => $this->getDescription(),
            "pricingScheme" => $this->getPricingScheme(),
            "cycles" => $this->getCycles(),
            "quantity" => $this->getQuantity(),
            "createdAt" => $this->getCreatedAt(),
            "updatedAt" => $this->getUpdatedAt(),
            "increment" => $this->getIncrement(),
            "mundipaggId" => $this->getMundipaggIdValue(),
        ];
    }

    /**
     * @return \Mundipagg\Core\Recurrence\ValueObjects\PricingSchemeValueObject
     */
    public function getPricingScheme()
    {
        return $this->pricingScheme;
    }

    /**
     * @param PricingSchemeValueObject $pricingScheme
     * @return SubProduct
     */
    public function setPricingScheme(PricingSchemeValueObject $pricingScheme)
    {
        $this->pricingScheme = $pricingScheme;
        return $this;
    }

    /**
     * @return string
     */
    public function getRecurrenceType()
    {
        return $this->recurrenceType;
    }

    /**
     * @param string $recurrenceType
     * @return SubProduct
     */
    public function setRecurrenceType($recurrenceType)
    {
        $this->recurrenceType = $recurrenceType;
        return $this;
    }

    /**
     * @return \Mundipagg\Core\Recurrence\Aggregates\Repetition
     */
    public function getSelectedRepetition()
    {
        return $this->selectedRepetition;
    }

    /**
     * @param Repetition $selectedRepetition
     * @return SubProduct
     */
    public function setSelectedRepetition($selectedRepetition)
    {
        $this->selectedRepetition = $selectedRepetition;
        return $this;
    }

    /**
     * @return \stdClass
     */
    public function convertToSdkRequest()
    {
        $items = new \stdClass();
        $items->name = $this->getName();
        $items->description = $this->getDescription();
        $items->pricing_scheme = $this->getPricingScheme();
        $items->cycles = $this->getCycles();
        $items->quantity = $this->getQuantity();
        $items->plan_item_id = $this->getId();
        $items->id = $this->getMundipaggIdValue();
        $items->status = "active";
        /**
         * @todo Fix increments
         * Array must be createad in another place
         */
        if ($this->getIncrement()) {
            $items->increments[] = $this->getIncrement()->convertToSDKRequest();
        }

        return $items;
    }

    /**
     * @return string|null
     */
    public function getMundipaggIdValue()
    {
        if (empty($this->getMundipaggId())) {
            return null;
        }
        return $this->getMundipaggId()->getValue();
    }
}