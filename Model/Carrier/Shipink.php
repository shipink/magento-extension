<?php
declare(strict_types=1);

namespace Shipink\Shipink\Model\Carrier;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Tracking\Result\StatusFactory;
use Psr\Log\LoggerInterface;
use Shipink\Shipink\Model\TrackingUrlLookup;

/**
 * Tracking-only carrier: shipments Shipink creates use carrier code
 * "shipink", so Magento's "Track your order" popup and shipment pages link to
 * the Shipink tracking page. Never offered at checkout in v1.
 */
class Shipink extends AbstractCarrier implements CarrierInterface
{
    /** @var string */
    protected $_code = 'shipink';

    /** @var bool */
    protected $_isFixed = true;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param ErrorFactory $rateErrorFactory
     * @param LoggerInterface $logger
     * @param StatusFactory $trackStatusFactory
     * @param TrackingUrlLookup $trackingUrlLookup
     * @param array $data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        private readonly StatusFactory $trackStatusFactory,
        private readonly TrackingUrlLookup $trackingUrlLookup,
        array $data = []
    ) {
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * Returns no rates; this carrier is tracking-only.
     *
     * @param RateRequest $request
     * @return bool
     */
    public function collectRates(RateRequest $request)
    {
        return false;
    }

    /**
     * Returns the single method this carrier declares.
     *
     * @return array
     */
    public function getAllowedMethods(): array
    {
        return ['shipink' => 'Shipink'];
    }

    /**
     * Tracking is always available for this carrier.
     *
     * @return bool
     */
    public function isTrackingAvailable(): bool
    {
        return true;
    }

    /**
     * Builds the tracking status, linking to the Shipink tracking page when a URL is known.
     *
     * @param string $tracking Tracking number.
     * @return \Magento\Shipping\Model\Tracking\Result\Status
     */
    public function getTrackingInfo($tracking)
    {
        $status = $this->trackStatusFactory->create();
        $status->setCarrier($this->_code);
        $status->setCarrierTitle((string)$this->getConfigData('title'));
        $status->setTracking($tracking);
        $url = $this->trackingUrlLookup->find((string)$tracking);
        if ($url !== null) {
            $status->setUrl($url);
        }
        return $status;
    }
}
