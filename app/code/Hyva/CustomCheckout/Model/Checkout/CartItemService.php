<?php
declare(strict_types=1);

namespace Hyva\CustomCheckout\Model\Checkout;

use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote\Item;

class CartItemService
{
    public function __construct(
        private readonly QuoteProvider $quoteProvider,
        private readonly ImageHelper $imageHelper,
    ) {
    }

    /**
     * @return array<int, array{item_id: int, name: string, qty: float, price: string, row_total: string, image: string, product_url: string}>
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getItems(): array
    {
        $quote = $this->quoteProvider->getActiveQuote();
        $items = [];

        foreach ($quote->getAllVisibleItems() as $item) {
            /** @var Item $item */
            $product = $item->getProduct();
            $items[] = [
                'item_id' => (int) $item->getId(),
                'name' => (string) $item->getName(),
                'qty' => (float) $item->getQty(),
                'price' => $quote->getStore()->getCurrentCurrency()->format(
                    (float) $item->getPrice(),
                    [],
                    false
                ),
                'row_total' => $quote->getStore()->getCurrentCurrency()->format(
                    (float) $item->getRowTotal(),
                    [],
                    false
                ),
                'image' => $product && $product->getId()
                    ? (string) $this->imageHelper->init($product, 'mini_cart_product_thumbnail')->getUrl()
                    : '',
                'product_url' => $product ? (string) $product->getProductUrl() : '',
            ];
        }

        return $items;
    }

    /**
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function updateQty(int $itemId, float $qty): void
    {
        $quote = $this->quoteProvider->getActiveQuote();

        foreach ($quote->getAllItems() as $item) {
            if ((int) $item->getId() === $itemId) {
                if ($qty <= 0) {
                    $quote->removeItem($itemId);
                } else {
                    $item->setQty($qty);
                }
                $quote->setTotalsCollectedFlag(false);
                $quote->collectTotals();
                $this->quoteProvider->saveQuote($quote);
                return;
            }
        }

        throw new LocalizedException(__('Cart item not found.'));
    }

    /**
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function removeItem(int $itemId): void
    {
        $this->updateQty($itemId, 0);
    }
}
