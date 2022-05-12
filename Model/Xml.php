<?php
declare(strict_types=1);

namespace MageSuite\GoogleReviewsFeed\Model;

class Xml
{
    /**
     * @var \Magento\Catalog\Model\Product[]
     */
    protected $productCache = [];

    protected \MageSuite\GoogleReviewsFeed\Model\ReviewList $reviewList;

    protected \Magento\Framework\Stdlib\DateTime\TimezoneInterface $date;

    protected \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory;

    protected \MageSuite\GoogleReviewsFeed\Helper\Configuration $configuration;

    protected \Laminas\Filter\HtmlEntities $htmlEntities;

    public function __construct(
        \MageSuite\GoogleReviewsFeed\Model\ReviewList $reviewList,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $date,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \MageSuite\GoogleReviewsFeed\Helper\Configuration $configuration,
        \Laminas\Filter\HtmlEntities $htmlEntities
    ) {
        $this->reviewList = $reviewList;
        $this->date = $date;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->configuration = $configuration;
        $this->htmlEntities = $htmlEntities;
    }

    public function execute(): string
    {
        $collection = $this->reviewList->getItems();
        $lastPage = $collection->getLastPageNumber();
        $page = 1;

        if (!$lastPage) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Unable to retrieve product reviews.')
            );
        }

        $domDocument = new \DOMDocument('1.0', 'UTF-8');
        $feed = $domDocument->appendChild($domDocument->createElement('feed'));
        $feed->setAttribute('xmlns:vc', 'http://www.w3.org/2007/XMLSchema-versioning');
        $feed->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $feed->setAttribute('xsi:noNamespaceSchemaLocation', 'http://www.google.com/shopping/reviews/schema/product/2.3/product_reviews.xsd');
        $feed->appendChild($domDocument->createElement('version', '2.3'));

        $publisherXml = $feed->appendChild($domDocument->createElement('publisher'));
        $nameElement = $domDocument->createElement('name', $this->filter($this->configuration->getPublisherName()));
        $faviconElement = $domDocument->createElement('favicon', $this->filter($this->configuration->getPublisherFaviconUrl()));
        $publisherXml->appendChild($nameElement);
        $publisherXml->appendChild($faviconElement);

        $reviewsXml = $feed->appendChild($domDocument->createElement('reviews'));

        while ($page <= $lastPage) {
            $collection->setCurPage($page)->load()->addRateVotes();

            foreach ($collection as $review) {
                $this->addReviewTag($domDocument, $reviewsXml, $review);
            }

            $collection->clear();
            $this->productCache = [];
            $page++;
        }

        return $domDocument->saveXML();
    }

    protected function addReviewTag(
        \DOMDocument $domDocument,
        \DOMElement $reviewsXml,
        \Magento\Review\Model\Review $review
    ): void {
        $reviewXml = $reviewsXml->appendChild($domDocument->createElement('review'));
        $reviewXml->appendChild($domDocument->createElement('review_id', $review->getId()));

        $this->addReviewerTag($domDocument, $reviewXml, $review);

        $reviewTimestamp = $this->date->date(strtotime($review->getCreatedAt()))->format('c');
        $reviewXml->appendChild($domDocument->createElement('review_timestamp', $reviewTimestamp));

        if (!empty($review->getTitle())) {
            $titleElement = $domDocument->createElement('title');
            $titleElement->appendChild($domDocument->createTextNode($this->filter($review->getTitle())));
            $reviewXml->appendChild($titleElement);
        }

        $contentElement = $domDocument->createElement('content');
        $contentElement->appendChild($domDocument->createCDATASection($this->filter($review->getDetail())));
        $reviewXml->appendChild($contentElement);

        $product = $this->getProduct($review);
        $reviewUrl = $reviewXml->appendChild($domDocument->createElement('review_url', $product->getProductUrl()));
        $reviewUrl->setAttribute('type', 'singleton');

        $this->addRatingsTag($domDocument, $reviewXml, $review);
        $this->addProductsTag($domDocument, $reviewXml, $review);
    }

    protected function addReviewerTag(
        \DOMDocument $domDocument,
        \DOMElement $reviewXml,
        \Magento\Review\Model\Review $review
    ): void {
        $reviewer = $reviewXml->appendChild($domDocument->createElement('reviewer'));
        $nickname = $this->modifyNickname($review);

        if (!empty($nickname)) {
            $nameElement = $domDocument->createElement('name');
            $nameElement->appendChild($domDocument->createCDATASection($nickname));
            $reviewer->appendChild($nameElement);
        } else {
            $reviewerName = $reviewer->appendChild($domDocument->createElement('name', 'Anonymous'));
            $reviewerName->setAttribute('is_anonymous', 'true');
        }

        if ($review->getCustomerId()) {
            $reviewer->appendChild($domDocument->createElement('reviewer_id', $review->getCustomerId()));
        }
    }

    protected function addRatingsTag(
        \DOMDocument $domDocument,
        \DOMElement $reviewXml,
        \Magento\Review\Model\Review $review
    ): void {
        $ratingElement = $reviewXml->appendChild($domDocument->createElement('ratings'));
        $percent = 0;

        if ($review->getRatingVotes() && $review->getRatingVotes()->getFirstItem()) {
            $percent = $review->getRatingVotes()->getFirstItem()->getPercent();
        }

        $rating = $percent / 20;
        $ratingOverall = $ratingElement->appendChild($domDocument->createElement('overall', (string)$rating));
        $ratingOverall->setAttribute('min', '1');
        $ratingOverall->setAttribute('max', '5');
    }

    public function addProductsTag(
        \DOMDocument $domDocument,
        \DOMElement $reviewXml,
        \Magento\Review\Model\Review $review
    ): void {
        $product = $this->getProduct($review);
        $childProduct = $this->getChildProduct($product);

        $productsXml = $reviewXml->appendChild($domDocument->createElement('products'));
        $productXml = $productsXml->appendChild($domDocument->createElement('product'));
        $productIdsXml = $productXml->appendChild($domDocument->createElement('product_ids'));

        $ean = $childProduct->getData('ean');

        if (!empty($ean)) {
            $gtinsXml = $productIdsXml->appendChild($domDocument->createElement('gtins'));
            $gtinsXml->appendChild($domDocument->createElement('gtin', $this->filter($ean)));
        }

        $sku = $childProduct->getData('sku');

        if (!empty($sku)) {
            $skusXml = $productIdsXml->appendChild($domDocument->createElement('skus'));
            $skusXml->appendChild($domDocument->createElement('sku', $this->filter($sku)));
        }

        $brand = $childProduct->getAttributeText('brand');

        if (!empty($brand)) {
            $brandsXml = $productIdsXml->appendChild($domDocument->createElement('brands'));
            $brandElement = $domDocument->createElement('brand');
            $brandElement->appendChild($domDocument->createCDATASection($brand));
            $brandsXml->appendChild($brandElement);
        }

        $productNameElement = $domDocument->createElement('product_name');
        $productNameElement->appendChild($domDocument->createTextNode($this->filter($product->getName())));
        $productXml->appendChild($productNameElement);

        $productUrl = $product->getProductUrl();
        $productXml->appendChild($domDocument->createElement('product_url', $productUrl));
    }

    protected function getChildProduct(\Magento\Catalog\Api\Data\ProductInterface $product): ?\Magento\Catalog\Model\Product
    {
        if (!$product->isComposite()) {
            return $product;
        }

        $childrenIds = $product->getTypeInstance()->getChildrenIds($product->getId());
        $entityIds = reset($childrenIds);
        $collection = $this->productCollectionFactory->create()
            ->addAttributeToFilter('entity_id', ['in' => $entityIds])
            ->addAttributeToSelect(['ean', 'brand'])
            ->setStoreId($product->getStoreId())
            ->setPageSize(1);

        return $collection->getFirstItem();
    }

    protected function modifyNickname(\Magento\Review\Model\Review $review): string
    {
        $nickname = trim($review->getNickname());

        if (strpos($nickname, '@') !== false) {
            return '';
        }

        if (strpos($nickname, ' ') !== false) {
            list($firstname, $lastname) = explode(' ', $nickname, 2);
            $nickname = sprintf('%s %s.', $firstname, $lastname[0]);
        }

        return $this->filter($nickname);
    }

    protected function getProduct(\Magento\Review\Model\Review $review): \Magento\Catalog\Model\Product
    {
        $productId = $review->getEntityPkValue();

        if (isset($this->productCache[$productId])) {
            return $this->productCache[$productId];
        }

        $collection = $this->productCollectionFactory->create()
            ->addAttributeToFilter('entity_id', $productId)
            ->addAttributeToSelect(['ean', 'brand', 'name'])
            ->setStoreId($review->getStoreId())
            ->setPageSize(1)
            ->addUrlRewrite();
        $this->productCache[$productId] = $collection->getFirstItem();

        return $this->productCache[$productId];
    }

    public function filter(string $value): string
    {
        return $this->htmlEntities->filter($value);
    }
}

