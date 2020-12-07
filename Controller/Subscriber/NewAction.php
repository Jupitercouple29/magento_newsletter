<?php
/**
 * Copyright (C) 2020  Newsletter Coupon
 * @package   Newsletter_Coupon
 */

namespace Newsletter\Coupon\Controller\Subscriber;

use Magento\Framework\App\ObjectManager;
use Magento\Newsletter\Model\Subscriber;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Framework\Controller\Result\Json;

/**
 * New newsletter subscription action
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class NewAction extends \Magento\Newsletter\Controller\Subscriber\NewAction
{
    /**
     * @var Json
     */
    protected $resultJson;

    /**
     * New subscription action
     *
     * @throws LocalizedException
     * @return void
     */
    public function execute()
    {
        $result = [];
        $result['error'] = true;
        if ($this->getRequest()->isPost() && $this->getRequest()->getPost('email')) {
            $email = (string)$this->getRequest()->getPost('email');

            try {
                $this->validateEmailFormat($email);
                $this->validateGuestSubscription();
                $this->validateEmailAvailable($email);

                $subscriber = $this->_subscriberFactory->create()->loadByEmail($email);
                if ($subscriber->getId()
                    && (int) $subscriber->getSubscriberStatus() == Subscriber::STATUS_SUBSCRIBED
                ) {
                    $result['message'] = __('This email address is already subscribed.');
                } else {
                    $status = $this->_subscriberFactory->create()->subscribe($email);
                    $result['error'] = false;
                    $result['message'] = $this->getSuccessMessage($status);
                }
            } catch (LocalizedException $e) {
                $result['message'] = __('There was a problem with the subscription: %1', $e->getMessage());
            } catch (\Exception $e) {
                $result['message'] = $e->getMessage();
            }
        }
        return $this->getResultJson()->setData($result);
    }

    /**
     * Get success message
     *
     * @param int $status
     * @return Phrase
     */
    private function getSuccessMessage(int $status): Phrase
    {
        if ($status === Subscriber::STATUS_NOT_ACTIVE) {
            return __('The confirmation request has been sent.');
        }

        return __('Thank you for your subscription.');
    }

    /**
     * @return Json
     */
    protected function getResultJson()
    {
        if ($this->resultJson === null) {
            $this->resultJson = ObjectManager::getInstance()->get(Json::class);
        }
        return $this->resultJson;
    }
}
