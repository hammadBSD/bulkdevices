<?php

namespace BSD\Getaquote\Controller\Index;

use Laminas\Validator\NotEmpty;
use Laminas\Validator\EmailAddress;
use Magento\Framework\App\Action\Action;

class Index extends Action
{

    const XML_PATH_EMAIL_RECIPIENT = 'getaquote/email/recipient';
    const XML_PATH_EMAIL_SENDER = 'getaquote/email/sender';
    const XML_PATH_EMAIL_TEMPLATE = 'getaquote/email/template';

    const XML_PATH_EMAIL_REPLY_SUBJECT = 'getaquote/email_reply/subject';
    const XML_PATH_EMAIL_REPLY_MESSAGE = 'getaquote/email_reply/body';

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->_transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    public function execute()
    {
        $post = $this->getRequest()->getPostValue();
        if (!$post) {
            $this->_redirect('*/*/');
            return;
        }

        try {
            $postObject = new \Magento\Framework\DataObject();
            $postObject->setData($post);

            $error = false;

            $nameValidator = new NotEmpty();
            $emailValidator = new EmailAddress();
            $phoneValidator = new NotEmpty();

            if (!$nameValidator->isValid(trim($post['name']))) {
                $error = true;
            }
            if (!$phoneValidator->isValid(trim($post['phone']))) {
                $error = true;
            }
            if (!$emailValidator->isValid(trim($post['email']))) {
                $error = true;
            }

            if ($error) {
                throw new \Exception();
            }

            $this->inlineTranslation->suspend();

            /*Email Sending Start*/
            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $transport = $this->_transportBuilder
                ->setTemplateIdentifier($this->scopeConfig->getValue(self::XML_PATH_EMAIL_TEMPLATE, $storeScope))
                ->setTemplateOptions(
                    [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                        'store' => $this->storeManager->getStore()->getId(),
                    ]
                )
                ->setTemplateVars(['data' => $postObject])
                ->setFrom($this->scopeConfig->getValue(self::XML_PATH_EMAIL_SENDER, $storeScope))
                ->addTo($this->scopeConfig->getValue(self::XML_PATH_EMAIL_RECIPIENT, $storeScope))
                ->setReplyTo($post['email'])
                ->getTransport();

            $transport->sendMessage();
            $this->inlineTranslation->resume();
            /*Email Sending End*/

            /*Save Data Start*/
            $post['create_date'] = time();
            $model = $this->_objectManager->create('BSD\Getaquote\Model\Getaquote');
            $model->setData($post);
            $model->save();
            /*Save Data End*/

            $message = __('Thanks for contacting us with your comments and questions. We\'ll respond to you very soon.');

            echo $message;

            return;
        } catch (\Exception $e) {
            $message = __('We can\'t process your request right now. Sorry, that\'s all we know.');
            echo $message;
            return;
        }
    }
}