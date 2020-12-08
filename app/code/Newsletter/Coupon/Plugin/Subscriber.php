<?php
/**
 * Copyright (C) 2020  Newsletter Coupon
 * @package   Newsletter_Coupon
 */

declare(strict_types = 1);

namespace Newsletter\Coupon\Plugin;

use Magento\Newsletter\Model\Subscriber as NewsSubscriber;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\StoreManagerInterface;
use Magento\SalesRule\Model\CouponFactory;

/**
 * Class Subscriber
 *
 * @package Newsletter\Coupon\Plugin
 */
class Subscriber
{
    const XML_PATH_SUCCESS_EMAIL_TEMPLATE = 'newsletter/subscription/success_email_template';
    const XML_PATH_SUCCESS_EMAIL_IDENTITY = 'newsletter/subscription/success_email_identity';
    const NEWSLETTER_CART_RULE_PRICE_ID = 'newscoupon/general/cartrule';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var ScopeConfigInterface
     */
    protected $inlineTranslation;

    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var CouponFactory
     */
    protected $couponFactory;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param StateInterface $inlineTranslation
     * @param TransportBuilder $transportBuilder
     * @param CouponFactory $couponFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StateInterface $inlineTranslation,
        TransportBuilder $transportBuilder,
        CouponFactory $couponFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->inlineTranslation = $inlineTranslation;
        $this->transportBuilder = $transportBuilder;
        $this->couponFactory = $couponFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * Newsletter Subscriber Success email
     *
     * @param NewsSubscriber $subject
     * @param Closure $proceed
     * @param mixed $collection
     */
    public function aroundSendConfirmationSuccessEmail (
        NewsSubscriber $subject,
        \Closure $proceed
    ) {

        if ($subject->getImportMode()) {
            return $subject;
        }

        if (!$this->scopeConfig->getValue(
            self::XML_PATH_SUCCESS_EMAIL_TEMPLATE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ) || !$this->scopeConfig->getValue(
            self::XML_PATH_SUCCESS_EMAIL_IDENTITY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        )
        ) {
            return $subject;
        }
        $couponCode = null;
        $cartRuleId = $this->scopeConfig->getValue(
                self::NEWSLETTER_CART_RULE_PRICE_ID,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        if ($cartRuleId) {
            $couponCollection = $this->couponFactory->create()->getCollection()->addFieldToFilter('rule_id', $cartRuleId)->addFieldToFilter('used_in_newsletter', 1)->getData();
            if (count($couponCollection)) {
                $coupon = $this->couponFactory->create()->load($couponCollection['0']['coupon_id']);
                $couponCode = $coupon->getCode();
            }
        }

        $this->inlineTranslation->suspend();

        $this->transportBuilder->setTemplateIdentifier(
            $this->scopeConfig->getValue(
                self::XML_PATH_SUCCESS_EMAIL_TEMPLATE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
        )->setTemplateOptions(
            [
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $this->storeManager->getStore()->getId()
            ]
        )->setTemplateVars(
            ['subscriber' => $subject, 'couponcode' => $couponCode]
        )->setFrom(
            $this->scopeConfig->getValue(
                self::XML_PATH_SUCCESS_EMAIL_IDENTITY,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
        )->addTo(
            $subject->getEmail(),
            $subject->getName()
        );
        $transport = $this->transportBuilder->getTransport();
        $transport->sendMessage();

        $this->inlineTranslation->resume();
        $subject->setCouponCode($couponCode)->save();
        return $subject;
    }
}
