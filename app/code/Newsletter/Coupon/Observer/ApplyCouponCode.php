<?php
/**
 * Copyright (C) 2020  Newsletter Coupon
 * @package   Newsletter_Coupon
 */
namespace Newsletter\Coupon\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Customer\Model\SessionFactory as CustomerSessionFactory;

/**
 * Class ApplyCouponCode
 *
 * @package Newsletter\Coupon\Observer
 */
class ApplyCouponCode implements ObserverInterface
{

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var Subscriber
     */
    protected $newsSubscriber;

    /**
     * @param CustomerSession $customerSession
     * @param Subscriber $newsSubscriber
     */
    public function __construct (
        CustomerSessionFactory $customerSession,
        SubscriberFactory $subscriber
    ) {
        $this->customerSession = $customerSession;
        $this->subscriber = $subscriber;
    }

    public function execute(\Magento\Framework\Event\Observer $observer) {
        try {
            $quote = $observer->getEvent()->getQuote();
            if(!$quote->getCouponCode()){
                if ($this->customerSession->create()->isLoggedIn()) {
                    $emailAddress = $quote->getCustomerEmail();
                } else {
                    $emailAddress = $quote->getBillingAddress()->getEmail();
                }
                if ($emailAddress) {
                    $modelcoupon = $this->subscriber->create()->getCollection()->addFieldToFilter('subscriber_email', ['eq' => $emailAddress])->addFieldToFilter('is_coupon_applied', ['eq' => 0]);
                    if($modelcoupon->getSize() > 0){
                        $coupondata = $modelcoupon->getData();
                        $couponCode = $coupondata[0]['coupon_code'];
                        if($couponCode){
                            if ($quote->getItemsCount() && $quote->getStoreId()) {
                                $quote->setCouponCode($couponCode);
                            }
                        }
                    }
                }
            }
        } catch (\Exception $e) {}
    }
}
