<?php
declare(strict_types=1);

namespace BSD\Getaquote\Controller\Index;

use Laminas\Validator\NotEmpty;
use Laminas\Validator\EmailAddress;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\StoreManagerInterface;

class Formpost extends Action
{
    const XML_PATH_EMAIL_RECIPIENT = 'getaquote/email/recipient';
    const XML_PATH_EMAIL_SENDER = 'getaquote/email/sender';
    const XML_PATH_EMAIL_TEMPLATE = 'getaquote/email/template';

    /**
     * @var TransportBuilder
     */
    protected TransportBuilder $transportBuilder;

    /**
     * @var StateInterface
     */
    protected StateInterface $inlineTranslation;

    /**
     * @var ScopeConfigInterface
     */
    protected ScopeConfigInterface $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManager;

    /**
     * @param Context $context
     * @param TransportBuilder $transportBuilder
     * @param StateInterface $inlineTranslation
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->transportBuilder = $transportBuilder;
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
            $postObject = new DataObject();
            $postObject->setData($post);

            $error = false;

            $nameValidator = new NotEmpty();
            $phoneValidator = new NotEmpty();
            $emailValidator = new EmailAddress();

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
                throw new LocalizedException(__('Validation failed.'));
            }

            $this->inlineTranslation->suspend();

            /* Email Sending Start */
            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $transport = $this->transportBuilder
                ->setTemplateIdentifier($this->scopeConfig->getValue(self::XML_PATH_EMAIL_TEMPLATE, $storeScope))
                ->setTemplateOptions([
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $this->storeManager->getStore()->getId(),
                ])
                ->setTemplateVars(['data' => $postObject])
                ->setFrom($this->scopeConfig->getValue(self::XML_PATH_EMAIL_SENDER, $storeScope))
                ->addTo($this->scopeConfig->getValue(self::XML_PATH_EMAIL_RECIPIENT, $storeScope))
                ->setReplyTo($post['email'])
                ->getTransport();

            $transport->sendMessage();
            $this->inlineTranslation->resume();
            /* Email Sending End */

            /* Save Data Start */
            $post['create_date'] = time();
            $model = $this->_objectManager->create('BSD\Getaquote\Model\Getaquote');
            $model->setData($post);
            $model->save();
            /* Save Data End */

            $this->messageManager->addSuccessMessage(__('Thanks for contacting us with your comments and questions. We\'ll respond to you very soon.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('We can\'t process your request right now. Sorry, that\'s all we know.'));
        }

        $this->_redirect('*/*/');
    }
}