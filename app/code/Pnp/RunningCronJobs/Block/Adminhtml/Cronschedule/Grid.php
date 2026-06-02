<?php
namespace Pnp\RunningCronJobs\Block\Adminhtml\Cronschedule;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Pnp\RunningCronJobs\Model\cronscheduleFactory
     */
    protected $_cronscheduleFactory;

    /**
     * @var \Pnp\RunningCronJobs\Model\Status
     */
    protected $_status;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Pnp\RunningCronJobs\Model\cronscheduleFactory $cronscheduleFactory
     * @param \Pnp\RunningCronJobs\Model\Status $status
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Pnp\RunningCronJobs\Model\CronscheduleFactory $CronscheduleFactory,
        \Pnp\RunningCronJobs\Model\Status $status,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
        $this->_cronscheduleFactory = $CronscheduleFactory;
        $this->_status = $status;
        $this->moduleManager = $moduleManager;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('postGrid');
        $this->setDefaultSort('schedule_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(false);
        $this->setVarNameFilter('post_filter');
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_cronscheduleFactory->create()->getCollection()
            ->addFieldToFilter('status', ['eq' => 'running']);
        $this->setCollection($collection);

        parent::_prepareCollection();

        return $this;
    }

    /**
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'schedule_id',
            [
                'header' => __('ID'),
                'type' => 'number',
                'index' => 'schedule_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );


		
				$this->addColumn(
					'job_code',
					[
						'header' => __('Job Code'),
						'index' => 'job_code',
					]
				);
				
				$this->addColumn(
					'status',
					[
						'header' => __('Status'),
						'index' => 'status',
					]
				);
				
				$this->addColumn(
					'created_at',
					[
						'header' => __('Created At'),
						'index' => 'created_at',
						'type'      => 'datetime',
					]
				);

					
				$this->addColumn(
					'scheduled_at',
					[
						'header' => __('Scheduled At'),
						'index' => 'scheduled_at',
						'type'      => 'datetime',
					]
				);

					
				$this->addColumn(
					'executed_at',
					[
						'header' => __('Executed At'),
						'index' => 'executed_at',
						'type'      => 'datetime',
					]
				);

					
				$this->addColumn(
					'finished_at',
					[
						'header' => __('Finished_At'),
						'index' => 'finished_at',
						'type'      => 'datetime',
					]
				);

					


		

		
		   $this->addExportType($this->getUrl('runningcronjobs/*/exportCsv', ['_current' => true]),__('CSV'));
		   $this->addExportType($this->getUrl('runningcronjobs/*/exportExcel', ['_current' => true]),__('Excel XML'));

        $block = $this->getLayout()->getBlock('grid.bottom.links');
        if ($block) {
            $this->setChild('grid.bottom.links', $block);
        }

        return parent::_prepareColumns();
    }

	
    /**
     * @return $this
     */
    protected function _prepareMassaction()
    {

        $this->setMassactionIdField('schedule_id');
        //$this->getMassactionBlock()->setTemplate('Pnp_RunningCronJobs::cronschedule/grid/massaction_extended.phtml');
        $this->getMassactionBlock()->setFormFieldName('cronschedule');

        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Delete'),
                'url' => $this->getUrl('runningcronjobs/*/massDelete'),
                'confirm' => __('Are you sure?')
            ]
        );

//        $statuses = $this->_status->getOptionArray();

//        $this->getMassactionBlock()->addItem(
//            'status',
//            [
//                'label' => __('Change status'),
//                'url' => $this->getUrl('runningcronjobs/*/massStatus', ['_current' => true]),
//                'additional' => [
//                    'visibility' => [
//                        'name' => 'status',
//                        'type' => 'select',
//                        'class' => 'required-entry',
//                        'label' => __('Status'),
//                        'values' => $statuses
//                    ]
//                ]
//            ]
//        );


        return $this;
    }
		

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('runningcronjobs/*/index', ['_current' => true]);
    }

    /**
     * @param \Pnp\RunningCronJobs\Model\cronschedule|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
		return '#';
    }

	

}