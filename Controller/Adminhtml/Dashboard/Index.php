<?php
declare(strict_types=1);

namespace Shipink\Shipink\Controller\Adminhtml\Dashboard;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'Shipink_Shipink::shipink';

    public function __construct(Action\Context $context, private readonly PageFactory $pageFactory)
    {
        parent::__construct($context);
    }

    public function execute()
    {
        $page = $this->pageFactory->create();
        $page->setActiveMenu('Shipink_Shipink::shipink');
        $page->getConfig()->getTitle()->prepend(__('Shipink'));
        return $page;
    }
}
