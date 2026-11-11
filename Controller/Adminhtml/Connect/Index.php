<?php
declare(strict_types=1);

namespace Shipink\Shipink\Controller\Adminhtml\Connect;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;
use Shipink\Shipink\Model\Pure\UrlValidator;

/**
 * Landing page for the redirect from Shipink. It is exempt from the admin
 * secret URL key (Shipink cannot know it) and therefore only shows a
 * confirmation form; the connect itself happens in Confirm (form key).
 */
class Index extends Action implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'Shipink_Shipink::connect';

    /** @var string[] */
    protected $_publicActions = ['index'];

    public function __construct(
        Action\Context $context,
        private readonly PageFactory $pageFactory,
        private readonly UrlValidator $urlValidator,
        private readonly StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
    }

    public function execute()
    {
        $req = $this->getRequest();
        $state = (string)$req->getParam('state');
        $callback = (string)$req->getParam('callback_url');
        $return = (string)$req->getParam('return_url');

        if ($state === '' || !$this->urlValidator->isAllowedShipinkUrl($callback)
            || !$this->urlValidator->isAllowedShipinkUrl($return)) {
            $this->messageManager->addErrorMessage(__('Invalid Shipink connect request.'));
            return $this->resultRedirectFactory->create()->setPath('shipink/dashboard/index');
        }

        $page = $this->pageFactory->create();
        $page->setActiveMenu('Shipink_Shipink::shipink');
        $page->getConfig()->getTitle()->prepend(__('Connect to Shipink'));
        $block = $page->getLayout()->getBlock('shipink.connect');
        if (!$block) {
            $this->messageManager->addErrorMessage(__('Invalid Shipink connect request.'));
            return $this->resultRedirectFactory->create()->setPath('shipink/dashboard/index');
        }
        $storeUrl = rtrim((string)$this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_WEB, true), '/');
        $block->setData('state', $state)->setData('callback_url', $callback)->setData('return_url', $return)
            ->setData('store_url', $storeUrl);
        return $page;
    }
}
