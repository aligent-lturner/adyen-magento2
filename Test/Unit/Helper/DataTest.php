<?php
/**
 *
 * Adyen Payment module (https://www.adyen.com/)
 *
 * Copyright (c) 2015 Adyen BV (https://www.adyen.com/)
 * See LICENSE.txt for license details.
 *
 * Author: Adyen <magento@adyen.com>
 */

namespace Adyen\Payment\Tests\Helper;

class DataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Adyen\Payment\Helper\Data
     */
    private $dataHelper;

    private function getSimpleMock($originalClassName)
    {
        return $this->getMockBuilder($originalClassName)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function setUp(): void
    {
        $context = $this->getSimpleMock(\Magento\Framework\App\Helper\Context::class);
        $encryptor = $this->getSimpleMock(\Magento\Framework\Encryption\EncryptorInterface::class);
        $dataStorage = $this->getSimpleMock(\Magento\Framework\Config\DataInterface::class);
        $country = $this->getSimpleMock(\Magento\Directory\Model\Config\Source\Country::class);
        $moduleList = $this->getSimpleMock(\Magento\Framework\Module\ModuleListInterface::class);
        $billingAgreementCollectionFactory = $this->getSimpleMock(\Adyen\Payment\Model\ResourceModel
                                                                  \Billing\Agreement\CollectionFactory::class);
        $assetRepo = $this->getSimpleMock(\Magento\Framework\View\Asset\Repository::class);
        $assetSource = $this->getSimpleMock(\Magento\Framework\View\Asset\Source::class);
        $notificationFactory = $this->getSimpleMock(\Adyen\Payment\Model\ResourceModel
                                                    \Notification\CollectionFactory::class);
        $taxConfig = $this->getSimpleMock(\Magento\Tax\Model\Config::class);
        $taxCalculation = $this->getSimpleMock(\Magento\Tax\Model\Calculation::class);
        $productMetadata = $this->getSimpleMock(\Magento\Framework\App\ProductMetadata::class);
        $adyenLogger = $this->getSimpleMock(\Adyen\Payment\Logger\AdyenLogger::class);
        $storeManager = $this->getSimpleMock(\Magento\Store\Model\StoreManager::class);
        $cache = $this->getSimpleMock(\Magento\Framework\App\CacheInterface::class);
        $localeResolver = $this->getSimpleMock(\Magento\Framework\Locale\ResolverInterface::class);
        $config = $this->getSimpleMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $serializer = $this->getSimpleMock(\Magento\Framework\Serialize\SerializerInterface::class);
        $componentRegistrar = $this->getSimpleMock(\Magento\Framework
                                                   \Component\ComponentRegistrarInterface::class);
        $localeHelper = $this->getSimpleMock(\Adyen\Payment\Helper\Locale::class);
        $orderManagement = $this->getSimpleMock(\Magento\Sales\Api\OrderManagementInterface::class);
        $orderStatusHistoryFactory = $this->getSimpleMock(\Magento\Sales\Model\Order\Status\HistoryFactory::class);

        $this->dataHelper = new \Adyen\Payment\Helper\Data(
            $context,
            $encryptor,
            $dataStorage,
            $country,
            $moduleList,
            $billingAgreementCollectionFactory,
            $assetRepo,
            $assetSource,
            $notificationFactory,
            $taxConfig,
            $taxCalculation,
            $productMetadata,
            $adyenLogger,
            $storeManager,
            $cache,
            $localeResolver,
            $config,
            $serializer,
            $componentRegistrar,
            $localeHelper,
            $orderManagement,
            $orderStatusHistoryFactory
        );
    }

    public function testFormatAmount()
    {
        $this->assertEquals('1234', $this->dataHelper->formatAmount('12.34', 'EUR'));
        $this->assertEquals('1200', $this->dataHelper->formatAmount('12.00', 'USD'));
        $this->assertEquals('12', $this->dataHelper->formatAmount('12.00', 'JPY'));
    }

    public function testisPaymentMethodOpenInvoiceMethod()
    {
        $this->assertEquals(true, $this->dataHelper->isPaymentMethodOpenInvoiceMethod('klarna'));
        $this->assertEquals(true, $this->dataHelper->isPaymentMethodOpenInvoiceMethod('klarna_account'));
        $this->assertEquals(true, $this->dataHelper->isPaymentMethodOpenInvoiceMethod('afterpay'));
        $this->assertEquals(true, $this->dataHelper->isPaymentMethodOpenInvoiceMethod('afterpay_default'));
        $this->assertEquals(true, $this->dataHelper->isPaymentMethodOpenInvoiceMethod('ratepay'));
        $this->assertEquals(false, $this->dataHelper->isPaymentMethodOpenInvoiceMethod('ideal'));
        $this->assertEquals(true, $this->dataHelper->isPaymentMethodOpenInvoiceMethod('test_klarna'));
    }

    /**
     * @param string $expectedResult
     * @param string $pspReference
     * @param string $checkoutEnvironment
     * @dataProvider checkoutEnvironmentsProvider
     *
     */
    public function testGetPspReferenceSearchUrl($expectedResult, $pspReference, $checkoutEnvironment)
    {
        $pspSearchUrl = $this->dataHelper->getPspReferenceSearchUrl($pspReference, $checkoutEnvironment);
        $this->assertEquals($expectedResult, $pspSearchUrl);
    }

    public function testGetPspReferenceWithNoAdditions(){
        $this->assertEquals(
            ['pspReference' => '852621234567890A', 'suffix' => ''],
            $this->dataHelper->parseTransactionId('852621234567890A')
        );
        $this->assertEquals(
            ['pspReference' => '852621234567890A', 'suffix' => '-refund'],
            $this->dataHelper->parseTransactionId('852621234567890A-refund')
        );
        $this->assertEquals(
            ['pspReference' => '852621234567890A', 'suffix' => '-capture'],
            $this->dataHelper->parseTransactionId('852621234567890A-capture')
        );
        $this->assertEquals(
            ['pspReference' => '852621234567890A', 'suffix' => '-capture-refund'],
            $this->dataHelper->parseTransactionId('852621234567890A-capture-refund')
        );
    }

    public static function checkoutEnvironmentsProvider()
    {
        return [
            [
                'https://ca-test.adyen.com/ca/ca/accounts/showTx.shtml?pspReference=7914073381342284',
                '7914073381342284',
                'false'
            ],
            [
                'https://ca-live.adyen.com/ca/ca/accounts/showTx.shtml?pspReference=883580976999434D',
                '883580976999434D',
                'true'
            ]
        ];
    }
}
