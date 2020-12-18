<?php

namespace Mundipagg\Core\Recurrence\Services\ResponseHandlers;

use \Mundipagg\Core\Kernel\Aggregates\Charge;
use Mundipagg\Core\Kernel\Factories\OrderFactory;
use Mundipagg\Core\Payment\Aggregates\Customer;
use Mundipagg\Core\Recurrence\Repositories\ChargeRepository;
use Mundipagg\Core\Recurrence\Services\ResponseHandlers\AbstractResponseHandler;
use Mundipagg\Core\Kernel\Abstractions\AbstractDataService;
use Mundipagg\Core\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use Mundipagg\Core\Kernel\Aggregates\Order;
use Mundipagg\Core\Kernel\Repositories\OrderRepository;
use Mundipagg\Core\Kernel\Services\InvoiceService;
use Mundipagg\Core\Kernel\Services\LocalizationService;
use Mundipagg\Core\Kernel\Services\OrderService;
use Mundipagg\Core\Kernel\ValueObjects\InvoiceState;
use Mundipagg\Core\Kernel\ValueObjects\OrderState;
use Mundipagg\Core\Kernel\ValueObjects\OrderStatus;
use Mundipagg\Core\Kernel\ValueObjects\TransactionType;
use Mundipagg\Core\Payment\Aggregates\Order as PaymentOrder;
use Mundipagg\Core\Payment\Factories\SavedCardFactory;
use Mundipagg\Core\Payment\Repositories\CustomerRepository;
use Mundipagg\Core\Payment\Repositories\SavedCardRepository;
use Mundipagg\Core\Recurrence\Aggregates\Subscription;
use Mundipagg\Core\Recurrence\Factories\SubscriptionFactory;
use Mundipagg\Core\Recurrence\Repositories\SubscriptionRepository;

final class SubscriptionHandler extends AbstractResponseHandler
{
    private $order;

    /**
     * @param Order $createdOrder
     * @return mixed
     */
    public function handle(Subscription $subscription)
    {
        $status = $this->getSubscriptionStatusFromCharge($subscription);
        $statusHandler = 'handleSubscriptionStatus' . $status;

        $platformOrderStatus = $status;

        $this->logService->orderInfo(
            $subscription->getCode(),
            "Handling subscription status: " . $status
        );
        $charge = $subscription->getCurrentCharge();
        $chargeRepository = new ChargeRepository();
        $chargeRepository->save($charge);

        $orderFactory = new OrderFactory();
        $this->order =
            $orderFactory->createFromSubscriptionData(
                $subscription,
                $platformOrderStatus
            );

        $subscriptionRepository = new SubscriptionRepository();
        $subscriptionRepository->save($subscription);
        $this->saveCustomer($subscription->getCustomer());

        return $this->$statusHandler($subscription);
    }

    private function handleSubscriptionStatusPaid(Subscription $subscription)
    {
        $invoiceService = new InvoiceService();

        $order = $this->order;

        $cantCreateReason = $invoiceService->getInvoiceCantBeCreatedReason($order);
        $platformInvoice = $invoiceService->createInvoiceFor($order);
        if ($platformInvoice !== null) {
            $this->completePayment($order, $subscription, $platformInvoice);

            return true;
        }
        return $cantCreateReason;
    }

    private function handleSubscriptionStatusPending(Subscription $subscription)
    {
        $order = $this->order;

        $order->setStatus(OrderStatus::pending());
        $platformOrder = $subscription->getPlatformOrder();

        $i18n = new LocalizationService();
        $platformOrder->addHistoryComment(
            $i18n->getDashboard(
                'Subscription created at Mundipagg. Id: %s',
                $subscription->getMundipaggId()->getValue()
            )
        );

        $subscriptionRepository = new SubscriptionRepository();
        $subscriptionRepository->save($subscription);

        $orderService = new OrderService();
        $orderService->syncPlatformWith($order);
        return true;
    }

    private function handleSubscriptionStatusFailed(Subscription $subscription)
    {
        $order = $this->order;

        $order->setStatus(OrderStatus::canceled());

        $platformOrder = $subscription->getPlatformOrder();
        $platformOrder->setState(OrderState::canceled());
        $platformOrder->save();

        $i18n = new LocalizationService();
        $platformOrder->addHistoryComment(
            $i18n->getDashboard(
                'Subscription payment failed at Mundipagg. Id: %s',
                $subscription->getMundipaggId()->getValue()
            )
        );

        $subscriptionRepository = new SubscriptionRepository();
        $subscriptionRepository->save($subscription);

        $orderService = new OrderService();
        $orderService->syncPlatformWith($order);

        $platformOrder->addHistoryComment(
            $i18n->getDashboard('Subscription canceled.')
        );

        return true;
    }

    private function handleSubscriptionStatus(Order $order)
    {
        $platformOrder = $order->getPlatformOrder();
        $i18n = new LocalizationService();
        $platformOrder->addHistoryComment(
            $i18n->getDashboard(
                'Order waiting for online retries at Mundipagg.' .
                ' MundipaggId: ' . $order->getMundipaggId()->getValue()
            )
        );

        return $this->handleOrderStatusPending($order);
    }

    /**
     * @param Order $order
     * @param $invoice
     */
    private function completePayment(Order $order, Subscription $subscription, $invoice)
    {
        $invoice->setState(InvoiceState::paid());
        $invoice->save();
        $platformOrder = $order->getPlatformOrder();

        /**
         * @todo Check if we should create transactions
         */
        //$this->createCaptureTransaction($order);

        $order->setStatus(OrderStatus::processing());
        //@todo maybe an Order Aggregate should have a State too.
        $platformOrder->setState(OrderState::processing());

        $i18n = new LocalizationService();
        $platformOrder->addHistoryComment(
            $i18n->getDashboard('Subscription invoice paid.') . '<br>' .
            ' MundipaggId: ' . $subscription->getMundipaggId()->getValue() . '<br>' .
            $i18n->getDashboard('Invoice') . ': ' .
            $subscription->getInvoice()->getMundipaggId()->getValue()
        );

        $subscriptionRepository = new SubscriptionRepository();
        $subscriptionRepository->save($subscription);

        $orderService = new OrderService();
        $orderService->syncPlatformWith($order);
    }

    /**
     * @param PaymentOrder $paymentOrder
     */
    private function saveCustomer(Customer $customer)
    {
        //save only registered customers;
        if(empty($customer) || $customer->getCode() === null) {
            return;
        }

        $customerRepository = new CustomerRepository();

        if ($customerRepository->findByCode($customer->getCode()) !== null) {
            $customerRepository->deleteByCode($customer->getCode());
        }

        if (
            $customerRepository->findByMundipaggId($customer->getMundipaggId()) === null
        ) {
            $customerRepository->save($customer);
        }
    }

    private function getSubscriptionStatusFromCharge(Subscription $subscription)
    {
        $charge = $subscription->getCurrentCharge();
        return ucfirst($charge->getStatus()->getStatus());
    }
}