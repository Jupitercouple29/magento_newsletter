<?php
/**
 * Copyright (C) 2020  Newsletter Coupon
 * @package   Newsletter_Coupon
 */

namespace Newsletter\Coupon\Observer\Html;

/**
 *
 * Adds extra columns to the Manage Coupon Codes table of a sales rule.
 */
class BeforeBlockToHtml implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return null
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $grid = $observer->getBlock();

        /**
         * \Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit\Tab\Coupons\Grid
         */
        if ($grid instanceof \Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit\Tab\Coupons\Grid) {
            $grid->addColumnAfter(
                'button',
                [
                    'header' => __('Button'),
                    'sortable' => false,
                    'filter' => false,
                    'align' => 'center',
                    'renderer' => \Newsletter\Coupon\Block\Adminhtml\Page\Grid\Renderer\Action::class,
                    'header_css_class' => 'col-action',
                    'column_css_class' => 'col-action'
                ],
                'times_used'
            )->addColumnAfter(
                'status',
                [
                    'header' => __('Status'),
                    'index' => 'status',
                    'default' => '',
                    'width' => '30',
                    'align' => 'center'
                ],
                'button'
            );
        }
    }
}
