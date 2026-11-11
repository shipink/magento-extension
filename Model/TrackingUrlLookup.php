<?php
declare(strict_types=1);

namespace Shipink\Shipink\Model;

use Magento\Sales\Model\ResourceModel\Order\Shipment\Comment\CollectionFactory as CommentCollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Shipment\Track\CollectionFactory as TrackCollectionFactory;
use Shipink\Shipink\Model\Pure\TrackingUrlExtractor;

class TrackingUrlLookup
{
    public function __construct(
        private readonly TrackCollectionFactory $trackCollectionFactory,
        private readonly CommentCollectionFactory $commentCollectionFactory,
        private readonly TrackingUrlExtractor $extractor
    ) {
    }

    public function find(string $trackNumber): ?string
    {
        $track = $this->trackCollectionFactory->create()
            ->addFieldToFilter('track_number', $trackNumber)
            ->addFieldToFilter('carrier_code', 'shipink')
            ->setPageSize(1)
            ->getFirstItem();
        if (!$track->getId()) {
            return null;
        }
        $comments = [];
        $collection = $this->commentCollectionFactory->create()
            ->addFieldToFilter('parent_id', $track->getParentId())
            ->setOrder('entity_id', 'ASC');
        foreach ($collection as $comment) {
            $comments[] = (string)$comment->getComment();
        }
        return $this->extractor->extract($comments, $trackNumber);
    }
}
