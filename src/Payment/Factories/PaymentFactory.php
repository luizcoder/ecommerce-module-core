<?php

namespace Mundipagg\Core\Payment\Factories;

use Mundipagg\Core\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use Mundipagg\Core\Kernel\Aggregates\Configuration;
use Mundipagg\Core\Kernel\Services\InstallmentService;
use Mundipagg\Core\Kernel\ValueObjects\CardBrand;
use Mundipagg\Core\Kernel\ValueObjects\Id\CustomerId;
use Mundipagg\Core\Payment\Aggregates\Customer;
use Mundipagg\Core\Payment\Aggregates\Payments\AbstractCreditCardPayment;
use Mundipagg\Core\Payment\Aggregates\Payments\BoletoPayment;
use Mundipagg\Core\Payment\Aggregates\Payments\NewCreditCardPayment;
use Mundipagg\Core\Payment\Aggregates\Payments\NewDebitCardPayment;
use Mundipagg\Core\Payment\Aggregates\Payments\NewVoucherPayment;
use Mundipagg\Core\Payment\Aggregates\Payments\SavedCreditCardPayment;
use Mundipagg\Core\Payment\Aggregates\Payments\SavedVoucherCardPayment;
use Mundipagg\Core\Payment\ValueObjects\BoletoBank;
use Mundipagg\Core\Payment\ValueObjects\CardId;
use Mundipagg\Core\Payment\ValueObjects\CardToken;
use Mundipagg\Core\Payment\ValueObjects\CustomerType;
use Mundipagg\Core\Payment\ValueObjects\PaymentMethod;
use Mundipagg\Core\Payment\Aggregates\Payments\SavedDebitCardPayment;

final class PaymentFactory
{
    /** @var string[] */
    private $primitiveFactories;
    /** @var Configuration  */
    private $moduleConfig;
    /** @var string */
    private $cardStatementDescriptor;

    /** @var BoletoBank */
    private $boletoBank;

    /** @var string */
    private $boletoInstructions;

    public function __construct()
    {
        $this->primitiveFactories = [
            'createCreditCardPayments',
            'createBoletoPayments',
            'createVoucherPayments',
            'createDebitCardPayments',
        ];

        $this->moduleConfig = MPSetup::getModuleConfiguration();

        $this->cardStatementDescriptor = $this->moduleConfig->getCardStatementDescriptor();
        $this->boletoBank = BoletoBank::itau();
        $this->boletoInstructions = $this->moduleConfig->getBoletoInstructions();
    }

    public function createFromJson($json)
    {
        $data = json_decode($json);

        $payments = [];

        foreach ($this->primitiveFactories as $creator) {
            $payments = array_merge($payments, $this->$creator($data));
        }

        return $payments;
    }

    private function createCreditCardPayments($data)
    {
        $cardDataIndex = AbstractCreditCardPayment::getBaseCode();

        if (!isset($data->$cardDataIndex)) {
            return [];
        }

        $cardsData = $data->$cardDataIndex;

        $payments = [];
        foreach ($cardsData as $cardData) {
            $payments[] = $this->createBasePayments(
                $cardData,
                $cardDataIndex,
                $this->moduleConfig
            );
        }

        return $payments;
    }

    private function createDebitCardPayments($data)
    {
        $cardDataIndex = NewDebitCardPayment::getBaseCode();

        if (!isset($data->$cardDataIndex)) {
            return [];
        }

        $config = $this->moduleConfig->getDebitConfig();
        $cardsData = $data->$cardDataIndex;

        $payments = [];
        foreach ($cardsData as $cardData) {
            $payments[] = $this->createBasePayments(
                $cardData,
                $cardDataIndex,
                $config
            );
        }

        return $payments;
    }

    private function createBasePayments(
        $cardData,
        $cardDataIndex,
        $config
    )
    {
        $payment = $this->createBaseCardPayment($cardData, $cardDataIndex);

        if ($payment === null) {
            return;
        }

        $customer = $this->createCustomer($cardData);
        if ($customer !== null) {
            $payment->setCustomer($customer);
        }

        $brand = $cardData->brand;
        $payment->setBrand(CardBrand::$brand());

        $payment->setAmount($cardData->amount);
        $payment->setInstallments($cardData->installments);

        //setting amount with interest
        $payment->setAmount(
            $this->getAmountWithInterestForCreditCard(
                $payment,
                $config
            )
        );

        $payment->setCapture($config->isCapture());
        $payment->setStatementDescriptor($config->getCardStatementDescriptor());

        return $payment;
    }

    private function createVoucherPayments($data)
    {
        $cardDataIndex = NewVoucherPayment::getBaseCode();

        if (!isset($data->$cardDataIndex)) {
            return [];
        }

        $config = $this->moduleConfig
            ->getVoucherConfig();

        $cardsData = $data->$cardDataIndex;

        $payments = [];
        foreach ($cardsData as $cardData) {
            $payments[] = $this->createBasePayments(
                $cardData,
                $cardDataIndex,
                $config
            );
        }

        return $payments;
    }

    private function createCustomer($paymentData)
    {
        $multibuyerEnabled = MPSetup::getModuleConfiguration()->isMultiBuyer();
        if (empty($paymentData->customer) || !$multibuyerEnabled) {
            return null;
        }

        $customerFactory = new CustomerFactory();

        return $customerFactory->createFromJson(json_encode($paymentData->customer));
    }

    private function getAmountWithInterestForCreditCard(
        AbstractCreditCardPayment $payment,
        $config
    )
    {
        $installmentService = new InstallmentService();

        $validInstallments = $installmentService->getInstallmentsFor(
            null,
            $payment->getBrand(),
            $payment->getAmount(),
            $config
        );

        foreach ($validInstallments as $validInstallment) {
            if ($validInstallment->getTimes() === $payment->getInstallments()) {
                return $validInstallment->getTotal();
            }
        }

        throw new \Exception('Invalid installment number!');
    }

    private function createBoletoPayments($data)
    {
        $boletoDataIndex = BoletoPayment::getBaseCode();

        if (!isset($data->$boletoDataIndex)) {
            return [];
        }

        $boletosData = $data->$boletoDataIndex;

        $payments = [];
        foreach ($boletosData as $boletoData) {
            $payment = new BoletoPayment();

            $customer = $this->createCustomer($boletoData);
            if ($customer !== null) {
                $payment->setCustomer($customer);
            }

            $payment->setAmount($boletoData->amount);
            $payment->setBank($this->boletoBank);
            $payment->setInstructions($this->boletoInstructions);

            $payments[] = $payment;
        }

        return $payments;
    }

    /**
     * @param $identifier
     * @return AbstractCreditCardPayment|null
     */
    private function createBaseCardPayment($data, $method)
    {
        $identifier = $data->identifier;
        try {
            $cardToken = new CardToken($identifier);
            $payment =  $this->getNewPaymentMethod($method);
            $payment->setIdentifier($cardToken);

            if (isset($data->saveOnSuccess)) {
                $payment->setSaveOnSuccess($data->saveOnSuccess);
            }
            return $payment;
        } catch(\Exception $e) {

        } catch (\Throwable $e) {

        }

        try {
            $cardId = new CardId($identifier);
            $payment =  $this->getSavedPaymentMethod($method);
            $payment->setIdentifier($cardId);

            if (isset($data->cvvCard)) {
                $payment->setCvv($data->cvvCard);
            }

            $owner = new CustomerId($data->customerId);
            $payment->setOwner($owner);

            return $payment;
        } catch(\Exception $e) {

        } catch (\Throwable $e) {

        }

        return null;
    }

    /**
     * @param $method
     * @return SavedCreditCardPayment|SavedVoucherCardPayment|SavedDebitCardPayment
     * @todo Add voucher saved payment
     */
    private function getSavedPaymentMethod($method)
    {
        $payments = [
            PaymentMethod::CREDIT_CARD => new SavedCreditCardPayment(),
            PaymentMethod::DEBIT_CARD => new SavedDebitCardPayment(),
            PaymentMethod::VOUCHER => new SavedVoucherCardPayment(),
        ];

        if (isset($payments[$method])) {
            return $payments[$method];
        }

        throw new \Exception("payment method saved not found", 400);
    }

    /**
     * @param $method
     * @return NewCreditCardPayment|NewVoucherPayment
     */
    private function getNewPaymentMethod($method)
    {
        $payments = [
            PaymentMethod::CREDIT_CARD => new NewCreditCardPayment(),
            PaymentMethod::VOUCHER => new NewVoucherPayment(),
            PaymentMethod::DEBIT_CARD => new NewDebitCardPayment(),
        ];

        if (!empty($payments[$method])) {
            return $payments[$method];
        }

        return new NewCreditCardPayment();
    }
}