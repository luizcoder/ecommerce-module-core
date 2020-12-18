<?php

namespace Mundipagg\Core\Recurrence\Factories;

use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;
use Mundipagg\Core\Kernel\Interfaces\FactoryInterface;
use Mundipagg\Core\Recurrence\Aggregates\Repetition;
use Mundipagg\Core\Recurrence\Aggregates\SubProduct;
use Mundipagg\Core\Recurrence\ValueObjects\DiscountValueObject;
use Mundipagg\Core\Recurrence\ValueObjects\IntervalValueObject;
use Mundipagg\Core\Recurrence\ValueObjects\PlanItemId;
use Mundipagg\Core\Recurrence\ValueObjects\PricingSchemeValueObject as PricingScheme;

class SubProductFactory implements FactoryInterface
{
    /**
     * @var SubProduct
     */
    protected $subProduct;

    public function __construct()
    {
        $this->subProduct = new SubProduct();
    }
    /**
     *
     * @param array $postData
     * @return AbstractEntity
     * @throws \Exception
     */
    public function createFromPostData($postData)
    {
        if (!is_array($postData)) {
            return;
        }

        $this->setId($postData);
        $this->setMundipaggId($postData);
        $this->setProductId($postData);
        $this->setProductRecurrenceId($postData);
        $this->setRecurrenceType($postData);
        $this->setName($postData);
        $this->setDescription($postData);
        $this->setPricingScheme($postData);
        $this->setQuantity($postData);
        $this->setCycles($postData);
        $this->setCreatedAt($postData);
        $this->setUpdatedAt($postData);

        return $this->subProduct;
    }

    /**
     *
     * @param array $dbData
     * @return AbstractEntity
     */
    public function createFromDbData($dbData)
    {
        return $this->createFromPostData($dbData);
        // TODO: Implement createFromDbData() method.
    }

    public function setId($postData)
    {
        if (!empty($postData['id'])) {
            $this->subProduct->setId($postData['id']);
        }
    }

    public function setMundipaggId($postData)
    {
        if (!empty($postData['mundipagg_id'])) {
            $this->subProduct->setMundipaggId(
                new PlanItemId($postData['mundipagg_id'])
            );
        }
    }

    public function setProductId($postData)
    {
        if (!empty($postData['product_id'])) {
            $this->subProduct->setProductId($postData['product_id']);
        }
    }

    public function setProductRecurrenceId($postData)
    {
        if (!empty($postData['product_recurrence_id'])) {
            $this->subProduct->setProductRecurrenceId($postData['product_recurrence_id']);
        }
    }

    public function setRecurrenceType($postData)
    {
        if (!empty($postData['recurrence_type'])) {
            $this->subProduct->setRecurrenceType($postData['recurrence_type']);
        }
    }

    public function setName($postData)
    {
        if (!empty($postData['name'])) {
            $this->subProduct->setName($postData['name']);
        }
    }

    public function setDescription($postData)
    {
        if (!empty($postData['description'])) {
            $this->subProduct->setDescription($postData['description']);
        }
    }

    public function setPricingScheme($postData)
    {
        if (!empty($postData['price'])) {

            $schemeType = !empty($postData['price_type']) ? $postData['price_type'] : 'UNIT';
            $pricingScheme = PricingScheme::$schemeType($postData['price']);

            $this->subProduct->setPricingScheme($pricingScheme);
        }
    }

    public function setQuantity($postData)
    {
        if (!empty($postData['quantity'])) {
            $this->subProduct->setQuantity($postData['quantity']);
        }
    }

    public function setCycles($postData)
    {
        if (!empty($postData['cycles'])) {
            $this->subProduct->setCycles($postData['cycles']);
        }
    }

    public function setCreatedAt($postData)
    {
        if (!empty($postData['created_at'])) {
            $this->subProduct->setCreatedAt(new \Datetime($postData['created_at']));
        }
    }

    public function setUpdatedAt($postData)
    {
        if (!empty($postData['updated_at'])) {
            $this->subProduct->setUpdatedAt(new \Datetime($postData['updated_at']));
        }
    }
}