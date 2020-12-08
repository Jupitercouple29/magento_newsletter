<?php
/**
 * Copyright (C) 2020  Newsletter Coupon
 * @package   Newsletter_Coupon
 */

namespace Newsletter\Coupon\Controller\Adminhtml\Coupon;

use Magento\Backend\App\Action\Context;
use Magento\SalesRule\Model\CouponFactory;

/**
 * Select Coupon code to sent in newsletter subscription
 */
class Select extends \Magento\Backend\App\Action
{
    /**
     * @var CouponFactory
     */
    protected $couponFactory;

    /**
     * @param Context $context
     * @param CouponFactory $couponFactory
     */
    public function __construct(
        Context $context,
        CouponFactory $couponFactory
    ) {
        parent::__construct($context);
        $this->couponFactory = $couponFactory;
    }

    /**
     * Select action
     *
     * @return void
     */
    public function execute()
    {
        $requestData = $this->getRequest()->getParams();
         try {
            $couponCollection = $this->couponFactory->create()->getCollection()->addFieldToFilter('rule_id', $requestData['rule_id'])->addFieldToFilter('used_in_newsletter', 1)->getData();
            if (count($couponCollection) && $couponCollection['0']['coupon_id'] != $requestData['coupon_id']) {
                $this->couponFactory->create()->load($couponCollection['0']['coupon_id'])->setUsedInNewsletter(0)->save();
            }

            $this->couponFactory->create()->load($requestData['coupon_id'])->setUsedInNewsletter(1)->save();
            $this->messageManager->addSuccessMessage(__('Successfully selected.'));
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong with the selection.'));
        }

        /** @var \Magento\Framework\Controller\Result\Redirect $redirect */
        $redirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        $redirectUrl = $this->_redirect->getRedirectUrl();
        return $redirect->setUrl($redirectUrl);
    }
}
?>