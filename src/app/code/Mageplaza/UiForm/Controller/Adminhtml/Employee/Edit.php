<?php

namespace Mageplaza\UiForm\Controller\Adminhtml\Employee;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action;

class Edit extends Action
{
    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        return $resultPage;
    }
}
