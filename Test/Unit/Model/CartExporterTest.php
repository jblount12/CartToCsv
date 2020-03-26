<?php

declare(strict_types=1);

namespace Born\CartToCsv\Test\Unit\Model;

use Born\CartToCsv\Model\CartExporter;
use Exception;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\File\Write;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

class CartExporterTest extends TestCase
{
    /**
     * @var MockObject
     */
    private $customerSessionMock;
    /**
     * @var MockObject
     */
    private $quoteRepositoryMock;
    /**
     * @var MockObject
     */
    private $quoteMock;
    /**
     * @var MockObject
     */
    private $filesystemMock;
    /**
     * @var MockObject
     */
    private $quoteItemMock;
    /**
     * @var CartExporter
     */
    private $model;
    /**
     * @var MockObject
     */
    private $directoryWriteMock;
    /**
     * @var MockObject
     */
    private $fileWriteMock;
    /**
     * @var array
     */
    private $testData;
    /**
     * @var int
     */
    private $testQuoteId;

    /**
     * @throws ReflectionException
     */
    public function setUp()
    {
        // initiate testing data
        $this->testData = [
            'columns' => [
                'sku',
                'name',
                'qty',
            ],
            'data' => [
                'testSku',
                'testName',
                'testQty',
            ]
        ];
        $this->testQuoteId = 123;

        // create mock objects
        $this->customerSessionMock = $this->createMock(Session::class);
        $this->quoteRepositoryMock = $this->createMock(CartRepositoryInterface::class);
        $this->quoteMock = $this->createMock(Quote::class);
        $this->quoteItemMock = $this->createMock(Quote\Item::class);
        $this->filesystemMock = $this->createMock(Filesystem::class);
        $this->directoryWriteMock = $this->createMock(Filesystem\Directory\Write::class);
        $this->fileWriteMock = $this->createMock(Write::class);

        // create test model
        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            CartExporter::class,
            [
                'filesystem' => $this->filesystemMock,
                'session' => $this->customerSessionMock,
                'columns' => $this->testData['columns']
            ]
        );
    }

    /**
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function testGetCartCsvData(): void
    {
        $this->customerSessionMock->expects($this->once())
            ->method('getQuote')
            ->will($this->returnValue($this->quoteMock));

        $this->quoteMock->expects($this->once())
            ->method('getAllVisibleItems')
            ->will($this->returnValue([$this->quoteItemMock]));

        $this->quoteItemMock->expects($this->exactly(count($this->testData['columns'])))
            ->method('getData')
            ->willReturnOnConsecutiveCalls(
                'testSku',
                'testName',
                'testQty'
            );

        // assert csv contents as expected
        $this->assertEquals(array_values($this->testData), $this->model->getCartCsvData());
    }

    public function testGetCsvFilename(): void
    {
        $this->customerSessionMock->expects($this->once())
            ->method('getQuote')
            ->will($this->returnValue($this->quoteMock));

        $this->quoteMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($this->testQuoteId));

        try {
            $this->assertEquals(
                CartExporter::FILE_PREFIX . '_' . $this->testQuoteId . '.csv',
                $this->model->getCsvFilename()
            );
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testWriteCsv(): void
    {
        $this->filesystemMock->expects($this->any())
            ->method('getDirectoryWrite')
            ->will($this->returnValue($this->directoryWriteMock));

        $this->directoryWriteMock->expects($this->once())
            ->method('openFile')
            ->will($this->returnValue($this->fileWriteMock));

        try {
            $this->model->writeCsv($this->testQuoteId . '.csv', $this->testData);
        } catch (FileSystemException $e) {
            $this->fail($e->getMessage());
        }
    }
}
