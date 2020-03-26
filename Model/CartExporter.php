<?php

declare(strict_types=1);

namespace Born\CartToCsv\Model;

use Born\CartToCsv\Api\CartExporterInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;

class CartExporter implements CartExporterInterface
{
    const FILE_PREFIX = 'cart_export';

    /**
     * @var Filesystem
     */
    private $filesystem;
    /**
     * @var array
     */
    private $columns;
    /**
     * @var Session
     */
    private $session;

    public function __construct(
        Filesystem $filesystem,
        Session $session,
        array $columns = []
    ) {
        $this->filesystem = $filesystem;
        $this->session = $session;
        $this->columns = $columns;
    }

    /**
     * @inheritDoc
     */
    public function getCartCsvFile(): string
    {
        // write file
        $filename = $this->getCsvFilename();
        $this->writeCsv($filename, $this->getCartCsvData());
        // return new filename
        return $filename;
    }

    /**
     * @return string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getCsvFilename(): string
    {
        return self::FILE_PREFIX . '_' . $this->session->getQuote()->getId() . '.csv';
    }

    /**
     * @param $filename
     * @param $data
     * @throws FileSystemException
     */
    public function writeCsv($filename, $data): void
    {
        $directory = $this->filesystem->getDirectoryWrite(DirectoryList::TMP);
        $write = $directory->openFile($filename, 'w+');

        foreach ($data as $row) {
            $write->writeCsv($row);
        }

        // close file
        $write->close();
    }

    /**
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getCartCsvData(): array
    {
        $csvData = [];
        $csvData[] = $this->columns;

        // write quote item data to row
        $quoteItems = $this->session->getQuote()->getAllVisibleItems();
        foreach ($quoteItems as $quoteItem) {
            $row = [];
            foreach ($this->columns as $column) {
                $row[] = $quoteItem->getData($column);
            }
            $csvData[] = $row;
        }

        return $csvData;
    }
}
