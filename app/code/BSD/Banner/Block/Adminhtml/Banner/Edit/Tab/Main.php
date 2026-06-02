<?php
/**
 * Class Main
 *
 * PHP version 7
 *
 * @category BSD
 * @package  BSD_Banner
 * @author   BSD <magento@bsd-technologies.com>
 * @license  https://www.bsd-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.bsd-technologies.com
 */
namespace BSD\Banner\Block\Adminhtml\Banner\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Customer\Model\ResourceModel\Group\Collection as CustomerGroup;

/**
 * Class Main
 *
 * PHP version 7
 *
 * @category BSD
 * @package  BSD_Banner
 * @author   BSD <magento@bsd-technologies.com>
 * @license  https://www.bsd-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.bsd-technologies.com
 */
class Main extends Generic implements TabInterface
{

    /**
     * WysiwygConfig
     *
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $wysiwygConfig;

    /**
     * StoreManager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
     protected $systemStore;
    /**
     * @var CustomerGroup
     */
    protected $customerGroup;

    /**
     * Main constructor.
     *
     * @param \Magento\Backend\Block\Template\Context    $context       context
     * @param \Magento\Framework\Registry                $registry      registry
     * @param \Magento\Framework\Data\FormFactory        $formFactory   formFactory
     * @param \Magento\Cms\Model\Wysiwyg\Config          $wysiwygConfig wysiwygConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager  storeManager
     * @param array                                      $data          data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Store\Model\System\Store $systemStore,
        CustomerGroup $customerGroup,
        array $data = []
    ) {

        $this->wysiwygConfig = $wysiwygConfig;
        $this->storeManager = $storeManager;
        $this->systemStore =$systemStore;
        $this->customerGroup = $customerGroup;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare Form
     *
     * @return \Magento\Backend\Block\Widget\Form\Generic
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('current_model');
       /*
         * Checking if user have permissions to save information
         */
        $isElementDisabled = !$this->_isAllowedAction('BSD_Banner::edit_banner');

        /**
         * Data Form
         *
         * @var \Magento\Framework\Data\Form $form
        */
        $form = $this->_formFactory->create();

        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('Banner Information')]
        );

        $wysiwygConfig = $this->wysiwygConfig->getConfig(
            ['tab_id' => $this->getTabId()]
        );

        if ($model->getBannerId()) {
            $fieldset->addField('banner_id', 'hidden', ['name' => 'banner_id']);
        }

        $bannerType = $fieldset->addField(
            'banner_type',
            'select',
            [
                'label' => 'Banner Type',
                'name' => 'post[banner_type]',
                'required'  => true,
                'values' =>
                [
                    [
                        'value'    => '',
                        'label'    => 'Select Banner',
                    ],
                    [
                        'value' => 'Image',
                        'label' => 'Image',
                    ],
                    [
                        'value' => 'Video',
                        'label' => 'Video',
                    ],
                    [
                        'value' => 'Youtube',
                        'label' => 'Youtube',
                    ],
                    [
                        'value' => 'Vimeo',
                        'label' => 'Vimeo',
                    ],
                ],
            ]
        )->setAfterElementHtml(
            '
        <script>
            require([
                 "jquery",
            ], function($){

                $(document).ready(function () {
                    $(".field-banner_image").hide();
                    $(".field-banner_video").hide();
                    $(".field-banner_youtube").hide();
                    $(".field-banner_vimeo").hide();
                    $(".field-banner_video_thumb_image").hide();
                    $(".field-banner_video_autoplay").hide();
                    $(".field-banner_description").hide();

                    $(function() {

                        if ($("#banner_type").val() == "Image") {
                            $(".field-banner_image").show();
                            $(".field-banner_description").show();
                            $(".field-banner_video").hide();
                            $(".field-banner_youtube").hide();
                            $(".field-banner_vimeo").hide();
                            $(".field-banner_video_thumb_image").hide();
                            $(".field-banner_video_autoplay").hide();
                        }
                        if ($("#banner_type").val() == "Video") {
                            $(".field-banner_video").show();
                            $(".field-banner_video_autoplay").show();
                            $(".field-banner_video_thumb_image").show();
                            $(".field-banner_image").hide();
                            $(".field-banner_vimeo").hide();
                            $(".field-banner_youtube").hide();
                            $(".field-banner_description").hide();
                        }
                        if ($("#banner_type").val() == "Youtube") {
                            $(".field-banner_youtube").show();
                            $(".field-banner_video_autoplay").show();
                            $(".field-banner_video_thumb_image").hide();
                            $(".field-banner_image").hide();
                            $(".field-banner_vimeo").hide();
                            $(".field-banner_video").hide();
                            $(".field-banner_description").hide();
                        }
                         if ($("#banner_type").val() == "Vimeo") {
                            $(".field-banner_youtube").hide();
                            $(".field-banner_video_autoplay").hide();
                            $(".field-banner_video_thumb_image").hide();
                            $(".field-banner_image").hide();
                            $(".field-banner_vimeo").show();
                            $(".field-banner_video").hide();
                            $(".field-banner_description").hide();
                        }
                    });

                    $("#banner_type").on("change", function() {

                        if($("#banner_type").attr("value") == 0){
                            $(".field-banner_image").hide();
                            $(".field-banner_video").hide();
                            $(".field-banner_youtube").hide();
                            $(".field-banner_video_autoplay").hide();
                            $(".field-banner_vimeo").hide();
                            $(".field-banner_video_thumb_image").hide();
                            $(".field-banner_description").hide();
                        }

                        if($("#banner_type").attr("value") == "Image"){
                            $(".field-banner_image").show();
                            $(".field-banner_video").hide();
                            $(".field-banner_youtube").hide();
                            $(".field-banner_video_autoplay").hide();
                            $(".field-banner_vimeo").hide();
                            $(".field-banner_video_thumb_image").hide();
                            $(".field-banner_description").show();
                        }

                        if($("#banner_type").attr("value") == "Video"){
                            $(".field-banner_video").show();
                            $(".field-banner_video_autoplay").show();
                            $(".field-banner_video_thumb_image").show();
                            $(".field-banner_image").hide();
                            $(".field-banner_vimeo").hide();
                            $(".field-banner_youtube").hide();
                            $(".field-banner_description").hide();
                        }

                        if($("#banner_type").attr("value") == "Youtube"){
                            $(".field-banner_youtube").show();
                            $(".field-banner_video_autoplay").show();
                            $(".field-banner_video_thumb_image").hide();
                            $(".field-banner_image").hide();
                            $(".field-banner_vimeo").hide();
                            $(".field-banner_video").hide();
                            $(".field-banner_description").hide();
                        }
                        if ($("#banner_type").attr("value")  == "Vimeo") {
                            $(".field-banner_youtube").hide();
                            $(".field-banner_video_autoplay").hide();
                            $(".field-banner_video_thumb_image").hide();
                            $(".field-banner_image").hide();
                            $(".field-banner_vimeo").show();
                            $(".field-banner_video").hide();
                            $(".field-banner_description").hide();
                        }
                    });
                });
              });
       </script>
    '
        );

        if (is_array($model->getData('banner_image'))) {
            $model->setData(
                'banner_image',
                $model->getData('banner_image')['value']
            );
        }

        $bannerImage = $fieldset->addField(
            'banner_image',
            'image',
            [
                'title' => __('Image'),
                'label' => __('Image'),
                'name' => 'post[banner_image]',
                'required' => true,
                'value' => $model->getData('banner_image'),
                'note' => __('Note : Please upload image 1920 x 650 (width x height) size with jpg, jpeg, gif, png format'),
            ]
        )->setAfterElementHtml(
            '
        <script>
            require([
                 "jquery",
            ], function($){
                $(document).ready(function () {
                    if($("#banner_image").attr("value")){
                        $("#banner_image").removeClass("required-file");
                    }else{
                        $("#banner_image").addClass("required-file");
                    }
                    $( "#banner_image" ).attr( "accept", "image/x-png,image/gif,image/jpeg,image/jpg,image/png" );
                });
              });
       </script>
    '
        );

        if (is_array($model->getData('banner_video'))) {
            $model->setData(
                'banner_video',
                $model->getData('banner_video')['value']
            );
        }
        $bannerVideo = $fieldset->addField(
            'banner_video',
            'file',
            [
                'title' => __('Video'),
                'label' => __('Video'),
                'name' => 'post[banner_video]',
                'value' => $model->getData('banner_video'),
                'note' => __('Note : Please upload video with mp4 format'),
            ]
        )->setAfterElementHtml(
            '<div>
                <a href="'.  $this->getMediaUrl() . $model->getData('banner_video') . '" target="_blank">'
                    . substr($model->getData("banner_video"), strrpos($model->getData("banner_video"), "/")+1) . '
                </a>
            </div>
            <script>
                require([
                     "jquery",
                ], function($){
                    $(document).ready(function () {
                        if($("#banner_video").attr("value")){
                            $("#banner_video").removeClass("required-file");
                        }else{
                            $("#banner_video").addClass("required-file");
                        }
                        $( "#banner_video" ).attr( "accept", "video/mp4" );
                    });
                  });
            </script>'
        );

        $bannerVideoThumbImage = $fieldset->addField(
            'banner_video_thumb_image',
            'image',
            [
                'title' => __('Thumb Image'),
                'label' => __('Thumb Image'),
                'name' => 'post[banner_video_thumb_image]',
                'value' => $model->getData('banner_video_thumb_image'),
                'note' => __('Note : Please upload image 1920 x 650 (width x height) size with jpg, jpeg, gif, png format'),
            ]
        )->setAfterElementHtml(
            '
        <script>
            require([
                 "jquery",
            ], function($){
                $(document).ready(function () {
                    $( "#banner_video_thumb_image" ).attr( "accept", "image/x-png,image/gif,image/jpeg,image/jpg,image/png" );
                });
              });
       </script>
    '
        );
        $bannerYoutube = $fieldset->addField(
            'banner_youtube',
            'text',
            [
                'name' => 'post[banner_youtube]',
                'label' => __('Youtube'),
                'title' => __('Youtube'),
                'required' => true,
                'note' => __('Note : Please add "XXXXXXXXXXX" from https://www.youtube.com/watch?v=XXXXXXXXXXX'),
                'disabled' => $isElementDisabled
            ]
        )->setAfterElementHtml(
            '
        <script>
            require([
                 "jquery",
            ], function($){
                $(document).ready(function () {
                    if($("#banner_youtube").attr("value")){
                        $("#banner_youtube").removeClass("required-entry");
                    }else{
                        $("#banner_youtube").addClass("required-entry");
                    }
                });
              });
       </script>
    '
        );
        $bannerVimeo = $fieldset->addField(
            'banner_vimeo',
            'text',
            [
                'name' => 'post[banner_vimeo]',
                'label' => __('Vimeo'),
                'title' => __('Vimeo'),
                'required' => true,
                'note' => __('Note : Please add "XXXXXXXXXXX" from https://player.vimeo.com/video/XXXXXXXXXXX'),
                'disabled' => $isElementDisabled
            ]
        )->setAfterElementHtml(
            '
        <script>
            require([
                 "jquery",
            ], function($){
                $(document).ready(function () {
                    if($("#banner_vimeo").attr("value")){
                        $("#banner_vimeo").removeClass("required-entry");
                    }else{
                        $("#banner_vimeo").addClass("required-entry");
                    }
                });
              });
       </script>
    '
        );
        $fieldset->addField(
            'banner_video_autoplay',
            'select',
            [
                'name'      => 'post[banner_video_autoplay]',
                'label'     => __('Autoplay'),
                'title'     => __('Autoplay'),
                'values' => [
                                ['value'=>'1','label'=>'Yes'],
                                ['value'=>'0','label'=>'No'],
                          ],
                'required'  => false,
                'checked' => 'checked',
                'disabled' => $isElementDisabled
            ]
        );

        $bannerTitle = $fieldset->addField(
            'banner_title',
            'text',
            [
                'name' => 'post[banner_title]',
                'label' => __('Title'),
                'title' => __('Title'),
                'class' => 'validate-length maximum-length-30',
                'required' => false,
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'store_id',
            'multiselect',
            [
                'name'     => 'store_id[]',
                'label'    => __('Store Views'),
                'title'    => __('Store Views'),
                'required' => true,
                'values'   => $this->systemStore->getStoreValuesForForm(false, true),
            ]
        );

        $fieldset->addField(
            'customer_group_id',
            'multiselect',
            [
                'name'     => 'customer_group_id[]',
                'label'    => __('Customer Groups'),
                'title'    => __('Customer Groups'),
                'required' => true,
                'values'   =>$this->customerGroup->toOptionArray(),
            ]
        );

        $bannerLabelText = $fieldset->addField(
            'label_button_text',
            'text',
            [
                'name' => 'post[label_button_text]',
                'label' => __('Button Text'),
                'title' => __('Button Text'),
                'class' => 'validate-length maximum-length-35',
                'disabled' => $isElementDisabled
            ]
        );

        $bannerCallToAction = $fieldset->addField(
            'call_to_action',
            'text',
            [
                'name' => 'post[call_to_action]',
                'label' => __('Button Link'),
                'title' => __('Button Link'),
                'disabled' => $isElementDisabled
            ]
        );

        $contentField = $fieldset->addField(
            'banner_description',
            'editor',
            [
                'name' => 'post[banner_description]',
                'label' => __('Description'),
                'title' => __('Description'),
                'class' => ' validate-length maximum-length-335',
                'config' => $wysiwygConfig,
                'disabled' => $isElementDisabled,
                'validate-length' => true,
            ]
        );

        $fieldset->addField(
            'start_date',
            'date',
            [
                'name'        => 'post[start_date]',
                'label'       => __('Start Date'),
                'title'       => __('Start Date'),
                'date_format' => 'dd-MM-Y',
                'time_format' => 'HH:mm:ss',
                'style' => 'width: 200px',
                'disabled' => $isElementDisabled
            ]
        )->setAfterElementHtml(
            '
        <script>
            require([
                 "jquery",
                 "jquery/ui",
                 "mage/mage",
                 "mage/calendar"
            ], function($){
                $(document).ready(function () {
                  $("#start_date").calendar({
                    dateFormat: "dd-MM-Y",
                    timeFormat: "HH:mm:ss",
                    changeMonth: true,
                    changeYear: true,
                    showsTime: true,
                    timeInput: true,
                    minDate: 0.1,
                    onSelect: function(selected) {
                    $("#end_date").datepicker("option", "minDate", selected);
                    }
                  });
                });
              });
       </script>
    '
        );

        $fieldset->addField(
            'end_date',
            'date',
            [
                'name'        => 'post[end_date]',
                'label'       => __('End Date'),
                'title'       => __('End Date'),
                'date_format' => 'dd-MM-Y',
                'time_format' => 'HH:mm:ss',
                'style' => 'width: 200px',
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'position',
            'text',
            [
                'name' => 'post[position]',
                'label' => __('Position'),
                'title' => __('Position'),
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'is_active',
            'select',
            [
                'label' => __('Status'),
                'title' => __('Banner Status'),
                'name' => 'post[is_active]',
                'required' => true,
                'options' => $model->getAvailableStatuses(),
                'disabled' => $isElementDisabled
            ]
        );

        $this->setChild(
            'form_after',
            $this->getLayout()
                ->createBlock(\Magento\Backend\Block\Widget\Form\Element\Dependence::class)
                ->addFieldMap($bannerType->getHtmlId(), $bannerType->getName())
                ->addFieldMap($bannerImage->getHtmlId(), $bannerImage->getName())
                ->addFieldMap($bannerVideo->getHtmlId(), $bannerVideo->getName())
                ->addFieldMap($bannerTitle->getHtmlId(), $bannerTitle->getName())
                ->addFieldMap($bannerLabelText->getHtmlId(), $bannerLabelText->getName())
                ->addFieldMap($bannerCallToAction->getHtmlId(), $bannerCallToAction->getName())
                ->addFieldMap($bannerYoutube->getHtmlId(), $bannerYoutube->getName())
                ->addFieldMap($bannerVimeo->getHtmlId(), $bannerVimeo->getName())
                ->addFieldDependence($bannerImage->getName(), $bannerType->getName(), 'Image')
                ->addFieldDependence($bannerVideo->getName(), $bannerType->getName(), 'Video')
                ->addFieldDependence($bannerYoutube->getName(), $bannerType->getName(), 'Youtube')
                ->addFieldDependence($bannerVimeo->getName(), $bannerType->getName(), 'Vimeo')
                ->addFieldDependence($bannerTitle->getName(), $bannerType->getName(), 'Image')
                ->addFieldDependence($bannerLabelText->getName(), $bannerType->getName(), 'Image')
                ->addFieldDependence($bannerCallToAction->getName(), $bannerType->getName(), 'Image')
        );

        if (!$model->getId()) {
            $model->setData('is_active', $isElementDisabled ? '0' : '1');
        }

        // Setting custom renderer for content field to remove label column
        $renderer = $this->getLayout()->createBlock(
            \Magento\Backend\Block\Widget\Form\Renderer\Fieldset\Element::class
        );
        $contentField->setRenderer($renderer);

        $this->_eventManager->dispatch(
            'bsd_banner_post_edit_tab_main_prepare_form',
            ['form' => $form]
        );

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Return Base Media Url
     *
     * @return mixed
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getMediaUrl()
    {
        $mediaUrl = $this->storeManager
            ->getStore()
            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        return $mediaUrl;
    }

    /**
     * Generate spaces
     *
     * @param int $n no of spaces
     *
     * @return string
     */
    protected function _getSpaces($n)
    {
        $s = '';
        for ($i = 0; $i < $n; $i++) {
            $s .= '--- ';
        }

        return $s;
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Banner  Information');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Banner Information');
    }

    /**
     * Returns status flag about this tab can be shown or not
     *
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId resourceId
     *
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
