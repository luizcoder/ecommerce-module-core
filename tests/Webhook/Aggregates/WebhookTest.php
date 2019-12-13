<?php

namespace Mundipagg\Core\Test\Webhook\Aggregates;

use Mundipagg\Core\Recurrence\Aggregates\Charge;
use Mundipagg\Core\Webhook\Aggregates\Webhook;
use Mundipagg\Core\Webhook\ValueObjects\WebhookId;
use Mundipagg\Core\Webhook\ValueObjects\WebhookType;
use PHPUnit\Framework\TestCase;

class WebhookIdTests extends TestCase
{
    public function testWebHookObjectKernel()
    {
        $webhook = new Webhook();
        $webhook->setId(1);
        $webhook->setMundipaggId(new WebhookId('hook_xxxxxxxxxxxxxxxx'));
        $webhook->setType(WebhookType::fromPostType('charge.paid'));
        $webhook->setEntity(new Charge());
        $webhook->setComponent([]);

        $this->assertEquals(1, $webhook->getId());
        $this->assertEquals('hook_xxxxxxxxxxxxxxxx', $webhook->getMundipaggId()->getValue());
        $this->assertEquals('charge', $webhook->getType()->getEntityType());
        $this->assertEquals('paid', $webhook->getType()->getAction());
        $this->assertEquals('Kernel', $webhook->getComponent());
        $this->assertInstanceOf(Charge::class, $webhook->getEntity());
    }

    public function testWebHookObjectRecurrence()
    {
        $webhook = new Webhook();
        $webhook->setId(1);
        $webhook->setMundipaggId(new WebhookId('hook_xxxxxxxxxxxxxxxx'));
        $webhook->setType(WebhookType::fromPostType('subscription.create'));
        $webhook->setEntity(new Charge());
        $webhook->setComponent(['invoice']);

        $this->assertEquals(1, $webhook->getId());
        $this->assertEquals('hook_xxxxxxxxxxxxxxxx', $webhook->getMundipaggId()->getValue());
        $this->assertEquals('subscription', $webhook->getType()->getEntityType());
        $this->assertEquals('create', $webhook->getType()->getAction());
        $this->assertEquals('Recurrence', $webhook->getComponent());
    }
}
