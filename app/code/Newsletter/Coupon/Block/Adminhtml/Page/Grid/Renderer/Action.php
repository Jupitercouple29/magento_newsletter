<?php
/**
 * Copyright (C) 2020  Newsletter Coupon
 * @package   Newsletter_Coupon
 */
namespace Newsletter\Coupon\Block\Adminhtml\Page\Grid\Renderer;

use Magento\Backend\Block\Context;
use Magento\Framework\UrlInterface;

/**
 * Render coupon action
 */
class Action extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @param Context $context
     * @param UrlInterface $urlBuilder
     * @param array $data
     */
    public function __construct(
        Context $context,
        UrlInterface $urlBuilder,
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $data);
    }

    /**
     * Render action
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        if ($row->getData('used_in_newsletter')) {
            return __('Selected');
        }

        $param = [];
        $param['rule_id'] = $row->getData('rule_id');
        $param['coupon_id'] = $row->getData('coupon_id');
        $url = $this->urlBuilder->getUrl('news/coupon/select', $param);

        return '<a href="' . $url . '">' . __('Select') . '</a>';
    }
}
