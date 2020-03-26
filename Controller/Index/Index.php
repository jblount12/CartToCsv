<?php

declare(strict_types=1);

namespace Born\CartToCsv\Controller\Index;

use Born\CartToCsv\Api\CartExporterInterface;
use Exception;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;

class Index extends Action
{
    /**
     * @var CartExporterInterface
     */
    private $exporter;
    /**
     * @var FileFactory
     */
    private $fileFactory;

    public function __construct(
        Context $context,
        CartExporterInterface $exporter,
        FileFactory $fileFactory
    ) {
        $this->exporter = $exporter;
        $this->fileFactory = $fileFactory;
        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface
     */
    public function execute()
    {
        try {
            // create file and get filename
            $csv = $this->exporter->getCartCsvFile();
            // configure response
            $content = [
                'type' => 'filename',
                'value' => $csv,
                'rm' => 1 // remove after download
            ];
            // return resulting file
            return $this->fileFactory->create($csv, $content, DirectoryList::TMP);
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage(__($e->getMessage()));
            return $this->_redirect('checkout/cart/index');
        }
    }
}
