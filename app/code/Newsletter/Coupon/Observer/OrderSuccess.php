<?php
/**
 * Copyright (C) 2020  Newsletter Coupon
 * @package   Newsletter_Coupon
 */

namespace Newsletter\Coupon\Observer;

use Magento\Newsletter\Model\Subscriber;

/**
 * Class OrderSuccess
 *
 * @package Newsletter\Coupon\Observer
 */
class OrderSuccess implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var Subscriber
     */
    protected $newsSubscriber;

    /**
     * @param Subscriber $newsSubscriber
     */
    public function __construct(
        Subscriber $newsSubscriber
    ) {
        $this->newsSubscriber = $newsSubscriber;
    }
 
    /**
     * Update applied coupon.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer ) {
        $order = $observer->getEvent()->getOrder();
        $email = $order->getCustomerEmail();
        try {
            if (!empty($order->getCouponCode())) {
                $newsSubscribers = $this->newsSubscriber->getCollection()->addFieldToSelect('subscriber_id')->addFieldToFilter('subscriber_email', $email)->getData();
                if (count($newsSubscribers)) {
                    $newsSubscriber = $this->newsSubscriber->load($newsSubscribers[0]['subscriber_id']);
                    if ($newsSubscriber->getCouponCode() == $order->getCouponCode()) {
                        $newsSubscriber->setIsCouponApplied(1)->save();
                    }
                }
            }
        } catch (\Exception $e) {
            $logger->info($e->getMessage());
        }
    }
}