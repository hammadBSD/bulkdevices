<?php

declare(strict_types=1);

namespace BSD\Getaquote\Controller\Index;

use BSD\Getaquote\Model\GetaquoteFactory;
use BSD\Getaquote\Model\ResourceModel\Getaquote as ResourceModel;
use Laminas\Validator\NotEmpty;
use Laminas\Validator\EmailAddress;
use Laminas\Validator\Regex;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\StoreManagerInterface;

class Save implements ActionInterface
{
    const XML_PATH_EMAIL_RECIPIENT = 'getaquote/email/recipient';
    const XML_PATH_EMAIL_SENDER = 'getaquote/email/sender';
    const XML_PATH_EMAIL_TEMPLATE = 'getaquote/email/template';
    const XML_PATH_RECAPTCHA_SECRET_KEY = 'getaquote/recaptcha_settings/secret_key';
    const XML_PATH_RECAPTCHA_SITE_KEY = 'getaquote/recaptcha_settings/site_key';
    const XML_PATH_RECAPTCHA_SITE_VERIFY_URL = 'getaquote/recaptcha_settings/siteverify_url';
    const XML_PATH_RECAPTCHA_SCRIPT_URL = 'getaquote/recaptcha_settings/script_url';

    /**
     * @var TransportBuilder
     */
    private TransportBuilder $transportBuilder;

    /**
     * @var StateInterface
     */
    private StateInterface $inlineTranslation;

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var RequestInterface
     */
    private RequestInterface $request;

    /**
     * @var GetaquoteFactory
     */
    private GetaquoteFactory $getaquoteFactory;

    /**
     * @var ResourceModel
     */
    private ResourceModel $resourceModel;

    /**
     * @var ManagerInterface
     */
    private ManagerInterface $messageManager;

    /**
     * @var JsonFactory
     */
    private JsonFactory $jsonFactory;

    /**
     * @param TransportBuilder $transportBuilder
     * @param StateInterface $inlineTranslation
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param RequestInterface $request
     * @param GetaquoteFactory $getaquoteFactory
     * @param ResourceModel $resourceModel
     * @param ManagerInterface $messageManager
     * @param JsonFactory $jsonFactory
     */
    public function __construct(
        TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        RequestInterface $request,
        GetaquoteFactory $getaquoteFactory,
        ResourceModel $resourceModel,
        ManagerInterface $messageManager,
        JsonFactory $jsonFactory
    ) {
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->request = $request;
        $this->getaquoteFactory = $getaquoteFactory;
        $this->resourceModel = $resourceModel;
        $this->messageManager = $messageManager;
        $this->jsonFactory = $jsonFactory;
    }

    /**
     * Execute action
     *
     * @return Json
     */
    public function execute(): Json
    {
        $paramsData = $this->request->getParams();
        $response = [];

        if (!$paramsData) {
            return $this->jsonFactory->create()->setData(['success' => false, 'message' => 'Invalid data.']);
        }

        try {
            $postObject = new \Magento\Framework\DataObject();
            $postObject->setData($paramsData);

            $error = false;

            // Validators
            $nameValidator = new NotEmpty();
            $phoneValidator = new NotEmpty();
            $emailValidator = new EmailAddress();
            $phoneRegex = new Regex('/^[0-9\-\+\s]*$/'); // Add a regex for phone validation

            // Validate fields
            if (!$nameValidator->isValid(trim($paramsData['name']))) {
                $error = true;
                throw new LocalizedException(__('Name field is required.'));
            }
            if (!$phoneValidator->isValid(trim($paramsData['phone'])) || !$phoneRegex->isValid(trim($paramsData['phone']))) {
                $error = true;
                throw new LocalizedException(__('Phone number is invalid.'));
            }
            if (!$emailValidator->isValid(trim($paramsData['email']))) {
                $error = true;
                throw new LocalizedException(__('Email address is invalid.'));
            }

            // Validate reCAPTCHA
            if (empty($paramsData['recaptchaToken'])) {
                throw new LocalizedException(__('reCAPTCHA token is missing. Please try again.'));
            }

            $recaptchaSecretKey = $this->scopeConfig->getValue(self::XML_PATH_RECAPTCHA_SECRET_KEY, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $recaptchaToken = $paramsData['recaptchaToken'];
            $recaptchaUrl = $this->scopeConfig->getValue(self::XML_PATH_RECAPTCHA_SITE_VERIFY_URL, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $responseKey = file_get_contents($recaptchaUrl . '?secret=' . $recaptchaSecretKey . '&response=' . $recaptchaToken);
            $responseKeys = json_decode($responseKey, true);

            if (empty($responseKeys['success']) || ($responseKeys['score'] ?? 0) < 0.5) {
                throw new LocalizedException(__('reCAPTCHA verification failed. Please try again.'));
            }

            if ($error) {
                throw new \Exception('Validation failed.');
            }

            // Proceed to send email
            $this->inlineTranslation->suspend();

            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $transport = $this->transportBuilder
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
                ->setReplyTo($paramsData['email'])
                ->getTransport();

            $transport->sendMessage();
            $this->inlineTranslation->resume();

            // Save Data to the database
            $paramsData['create_date'] = time();
            $model = $this->getaquoteFactory->create();
            $model->setData($paramsData);
            $this->resourceModel->save($model);

            $this->messageManager->addSuccessMessage(__('Thanks for contacting us with your comments and questions. We\'ll respond to you very soon.'));
            $response = ['success' => true];

        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $response = ['success' => false, 'message' => $e->getMessage()];
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('We can\'t process your request right now. Sorry, that\'s all we know.'));
            $response = ['success' => false, 'message' => $e->getMessage()];
        }

        return $this->jsonFactory->create()->setData($response);
    }
}
