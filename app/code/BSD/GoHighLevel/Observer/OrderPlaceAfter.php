<?php

namespace BSD\GoHighLevel\Observer;

use BSD\GoHighLevel\Service\ContactOpportunityManager;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;

class OrderPlaceAfter implements ObserverInterface
{
    /**
     * @var ContactOpportunityManager
     */
    private $contactOpportunityManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ContactOpportunityManager $contactOpportunityManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        ContactOpportunityManager $contactOpportunityManager,
        LoggerInterface $logger
    ) {
        $this->contactOpportunityManager = $contactOpportunityManager;
        $this->logger = $logger;
    }

    /**
     * Execute observer
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $this->logger->info('GHL DEBUG: OrderPlaceAfter observer - execute method called');
        
        try {
            $order = $observer->getEvent()->getOrder();
            
            $this->logger->info('GHL DEBUG: OrderPlaceAfter - Order object: ' . ($order ? 'exists' : 'null'));
            
            if (!$order) {
                $this->logger->warning('GHL DEBUG: OrderPlaceAfter - No order object found');
                return;
            }
            
            // Order might not have ID yet in sales_order_place_after event, but should have increment ID
            $orderId = $order->getId();
            $incrementId = $order->getIncrementId();
            
            $this->logger->info('GHL DEBUG: OrderPlaceAfter - Order ID: ' . ($orderId ?: 'not set') . ', Increment ID: ' . ($incrementId ?: 'not set'));
            
            if (!$incrementId) {
                $this->logger->warning('GHL DEBUG: OrderPlaceAfter - No increment ID found, cannot process');
                return;
            }
            
            $this->logger->info('GHL DEBUG: OrderPlaceAfter - Processing order #' . $incrementId . ' (ID: ' . ($orderId ?: 'pending') . ')');

            $shippingAddress = $order->getShippingAddress();
            if (!$shippingAddress) {
                $shippingAddress = $order->getBillingAddress();
            }

            if (!$shippingAddress) {
                $this->logger->warning('GHL: No shipping or billing address found for order #' . $incrementId);
                return;
            }

            // Prepare data for GHL
            $data = [
                'email' => $order->getCustomerEmail(),
                'firstname' => $shippingAddress->getFirstname(),
                'lastname' => $shippingAddress->getLastname(),
                'phone' => $shippingAddress->getTelephone(),
                'address' => $shippingAddress->getStreetLine(1),
                'city' => $shippingAddress->getCity(),
                'state' => $shippingAddress->getRegion(),
                'postal_code' => $shippingAddress->getPostcode(),
                'country' => $shippingAddress->getCountryId(),
                'monetary_value' => $order->getGrandTotal(),
                'grand_total' => $order->getGrandTotal(),
                'items' => $this->getOrderItems($order),
                'increment_id' => $incrementId
            ];

            // Process contact and opportunity
            // Use order ID if available, otherwise use null (will be set later)
            $result = $this->contactOpportunityManager->processContactAndOpportunity(
                $data,
                'checkout',
                $orderId ?: null
            );

            if ($result['success']) {
                $this->logger->info('GHL: Successfully synced order #' . $incrementId . ' to GHL');
            } else {
                $this->logger->error('GHL: Failed to sync order #' . $incrementId . ': ' . ($result['message'] ?? 'Unknown error'));
            }
        } catch (\Exception $e) {
            $this->logger->error('GHL: Exception in OrderPlaceAfter observer: ' . $e->getMessage());
        }
    }

    /**
     * Get order items as array
     *
     * @param \Magento\Sales\Model\Order $order
     * @return array
     */
    private function getOrderItems($order)
    {
        $items = [];
        foreach ($order->getAllItems() as $item) {
            $items[] = [
                'sku' => $item->getSku(),
                'name' => $item->getName(),
                'qty' => $item->getQtyOrdered(),
                'price' => $item->getPrice()
            ];
        }
        return $items;
    }
}
