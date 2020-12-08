<?php
/**
 * Copyright (C) 2020  Newsletter Coupon
 * @package   Newsletter_Coupon
 */
namespace Newsletter\Coupon\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Model\SessionFactory as CustomerSessionFactory;
use Magento\Checkout\Model\SessionFactory as CheckoutsessionFactory;
use Magento\Newsletter\Model\SubscriberFactory;

/**
 * Class Applycouponcode
 *
 * @package Newsletter\Coupon\Observer
 */
class Applycouponcode implements ObserverInterface
{
    /**
     * @var CheckoutsessionFactory
     */
    protected $checkoutsessionFactory;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var Subscriber
     */
    protected $newsSubscriber;

    /**
     * @param CheckoutsessionFactory $checkoutsessionFactory
     * @param CustomerSession $customerSession
     * @param Subscriber $newsSubscriber
     */
    public function __construct (
        CheckoutsessionFactory $checkoutsessionFactory,
        CustomerSessionFactory $customerSession,
        SubscriberFactory $subscriber
    ) {

        $this->checkoutsessionFactory = $checkoutsessionFactory;
        $this->customerSession = $customerSession;
        $this->subscriber = $subscriber;

    }

    public function execute(\Magento\Framework\Event\Observer $observer) {
        $quote = $observer->getEvent()->getQuote();

        if($this->customerSession->create()->isLoggedIn()) {
            $emailAddress = $quote->getCustomerEmail();
        } else {
            $emailAddress = $quote->getBillingAddress()->getEmail();
        }
        $modelcoupon = $this->subscriber->create()->getCollection()->addFieldToFilter('subscriber_email', ['eq' => $emailAddress])->addFieldToFilter('is_coupon_applied', ['eq' => 0]);
        if($modelcoupon->getSize() > 0){
            $coupondata = $modelcoupon->getData();
            $couponCode = $coupondata[0]['coupon_code'];
            if($couponCode){
                if(!$quote->getCouponCode()){
                    $this->checkoutsessionFactory->create()->getQuote()->setCouponCode($couponCode)
                              ->collectTotals()
                              ->save();
                }
            }
        }
    }
}
