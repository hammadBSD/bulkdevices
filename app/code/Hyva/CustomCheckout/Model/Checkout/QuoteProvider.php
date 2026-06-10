<?php
declare(strict_types=1);

namespace Hyva\CustomCheckout\Model\Checkout;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;

class QuoteProvider
{
    public function __construct(
        private readonly CheckoutSession $checkoutSession,
        private readonly CartRepositoryInterface $cartRepository,
    ) {
    }

    /**
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getActiveQuote(): Quote
    {
        $quote = $this->checkoutSession->getQuote();

        if (!$quote->getId() || !$quote->getIsActive() || $quote->getItemsCount() < 1) {
            throw new LocalizedException(__('Your cart is empty.'));
        }

        return $quote;
    }

    /**
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function saveQuote(Quote $quote): void
    {
        $this->cartRepository->save($quote);
        $this->checkoutSession->replaceQuote($quote);
    }
}
