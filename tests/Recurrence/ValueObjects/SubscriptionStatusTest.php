<?php

namespace Mundipagg\Core\Test\Recurrence\ValueObjects;

use Mundipagg\Core\Recurrence\ValueObjects\SubscriptionStatus;
use PHPUnit\Framework\TestCase;

class SubscriptionStatusTest extends TestCase
{
    public function testSubscriptionStatusActive()
    {
        $this->assertEquals('active', SubscriptionStatus::active()->getStatus());
    }

    public function testSubscriptionStatusCanceled()
    {
        $this->assertEquals('canceled', SubscriptionStatus::canceled()->getStatus());
    }

    public function testSubscriptionStatusFuture()
    {
        $this->assertEquals('future', SubscriptionStatus::future()->getStatus());
    }

    public function testSubscriptionStatusIsEquals()
    {
        $subscriptionStatusFuture = SubscriptionStatus::future();

        $this->assertTrue($subscriptionStatusFuture->equals(SubscriptionStatus::future()));
    }

    public function testSubscriptionJsonSerialize()
    {
        $subscriptionStatusFuture = SubscriptionStatus::future()->jsonSerialize();
        $this->assertEquals('future', $subscriptionStatusFuture);
    }
}
