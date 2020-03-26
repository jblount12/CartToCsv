<?php

declare(strict_types=1);

namespace Born\CartToCsv\Api;

use Exception;
use Magento\Framework\Exception\FileSystemException;

/**
 * Interface CartExporterInterface
 * @api
 */
interface CartExporterInterface
{
    /**
     * @return string
     * @throws FileSystemException|Exception
     */
    public function getCartCsvFile(): string;
}
