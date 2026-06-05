<?php

namespace BSD\GoHighLevel\Plugin\Cron;

use BSD\GoHighLevel\Service\ContactOpportunityManager;
use Magento\Cron\Model\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class ConfigPlugin
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Modify cron job schedule dynamically from configuration
     *
     * @param Config $subject
     * @param array $result
     * @return array
     */
    public function afterGetJobs(Config $subject, $result)
    {
        if (!is_array($result)) {
            return $result;
        }

        // Get the schedule from configuration
        $cronSchedule = trim($this->scopeConfig->getValue(
            ContactOpportunityManager::XML_PATH_CRON_SCHEDULE,
            ScopeInterface::SCOPE_STORE
        ));

        // Update the schedule for our specific cron job if configured
        // If not configured, it will use the default from crontab.xml
        if (!empty($cronSchedule) && isset($result['default']['bsd_ghl_sync_abandoned_checkouts'])) {
            $result['default']['bsd_ghl_sync_abandoned_checkouts']['schedule'] = $cronSchedule;
        }

        return $result;
    }
}
