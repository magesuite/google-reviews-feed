<?php
declare(strict_types=1);

namespace MageSuite\GoogleReviewsFeed\Model;

class Xml
{
    protected \MageSuite\GoogleReviewsFeed\Model\ReviewList $reviewList;

    protected \MageSuite\GoogleReviewsFeed\Model\ProductDataRepository $productDataRepository;

    protected \MageSuite\GoogleReviewsFeed\Model\NicknameModifier $nicknameModifier;

    protected \MageSuite\GoogleReviewsFeed\Helper\Configuration $configuration;

    protected \Magento\Framework\Stdlib\DateTime\TimezoneInterface $date;

    protected \Laminas\Filter\HtmlEntities $htmlEntities;

    public function __construct(
        \MageSuite\GoogleReviewsFeed\Model\ReviewList $reviewList,
        \MageSuite\GoogleReviewsFeed\Model\ProductDataRepository $productDataRepository,
        \MageSuite\GoogleReviewsFeed\Model\NicknameModifier $nicknameModifier,
        \MageSuite\GoogleReviewsFeed\Helper\Configuration $configuration,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $date,
        \Laminas\Filter\HtmlEntities $htmlEntities
    ) {
        $this->reviewList = $reviewList;
        $this->productDataRepository = $productDataRepository;
        $this->nicknameModifier = $nicknameModifier;
        $this->configuration = $configuration;
        $this->date = $date;
        $this->htmlEntities = $htmlEntities;
    }

    public function execute(): string
    {
        $collection = $this->reviewList->getCollection();
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
            $collection->setCurPage($page)->load();
            $this->productDataRepository->processReviewCollection($collection);

            foreach ($collection as $review) {
                if (!$this->hasAllRequiredAttributesValues($review)) {
                    continue;
                }

                $this->addReviewTag($domDocument, $reviewsXml, $review);
            }

            $collection->clear();
            $page++;
        }

        return $domDocument->saveXML();
    }

    protected function hasAllRequiredAttributesValues(\Magento\Review\Model\Review $review): bool
    {
        $productData = $this->getProductData($review);
        $childProduct = $this->getChildProductData($productData, $review);
        $attributes = ['gtin', 'sku', 'brand'];

        foreach ($attributes as $attribute) {
            $methodName = 'get' . ucfirst($attribute);

            if (!empty($childProduct->$methodName())) {
                continue;
            }

            return false;
        }

        return true;
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
            $titleElement->appendChild($domDocument->createTextNode($review->getTitle()));
            $reviewXml->appendChild($titleElement);
        }

        $contentElement = $domDocument->createElement('content');
        $contentElement->appendChild($domDocument->createCDATASection($review->getDetail()));
        $reviewXml->appendChild($contentElement);

        $productData = $this->getProductData($review);
        $reviewUrl = $reviewXml->appendChild($domDocument->createElement('review_url', $productData->getUrl()));
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
        $rating = $review->getData('rating_percent') / 20;
        $rating = max(1, $rating);
        $ratingOverall = $ratingElement->appendChild($domDocument->createElement('overall', (string)$rating));
        $ratingOverall->setAttribute('min', '1');
        $ratingOverall->setAttribute('max', '5');
    }

    public function addProductsTag(
        \DOMDocument $domDocument,
        \DOMElement $reviewXml,
        \Magento\Review\Model\Review $review
    ): void {
        $productData = $this->getProductData($review);
        $childProduct = $this->getChildProductData($productData, $review);

        $productsXml = $reviewXml->appendChild($domDocument->createElement('products'));
        $productXml = $productsXml->appendChild($domDocument->createElement('product'));
        $productIdsXml = $productXml->appendChild($domDocument->createElement('product_ids'));

        $gtin = $childProduct->getGtin();
        $gtinsXml = $productIdsXml->appendChild($domDocument->createElement('gtins'));
        $gtinsXml->appendChild($domDocument->createElement('gtin', $this->filter($gtin)));

        $sku = $childProduct->getSku();
        $skusXml = $productIdsXml->appendChild($domDocument->createElement('skus'));
        $skusXml->appendChild($domDocument->createElement('sku', $this->filter($sku)));

        $brand = $childProduct->getBrand();

        if (!empty($brand)) {
            $brandsXml = $productIdsXml->appendChild($domDocument->createElement('brands'));
            $brandElement = $domDocument->createElement('brand');
            $brandElement->appendChild($domDocument->createCDATASection($brand));
            $brandsXml->appendChild($brandElement);
        }

        $productNameElement = $domDocument->createElement('product_name');
        $productNameElement->appendChild($domDocument->createTextNode($this->filter($childProduct->getName())));
        $productXml->appendChild($productNameElement);

        $productXml->appendChild($domDocument->createElement('product_url', $productData->getUrl()));
    }

    protected function getProductData(\Magento\Review\Model\Review $review): \MageSuite\GoogleReviewsFeed\Model\ProductData
    {
        $productId = $review->getEntityPkValue();

        return $this->productDataRepository->getProductData($productId, $review->getStoreId());
    }

    protected function getChildProductData(
        \MageSuite\GoogleReviewsFeed\Model\ProductData $productData,
        \Magento\Review\Model\Review $review
    ): \MageSuite\GoogleReviewsFeed\Model\ProductData {
        $childrenIds = $productData->getChildrenIds();

        if (!$productData->isComposite() || empty($childrenIds)) {
            return $productData;
        }

        foreach ($childrenIds as $childrenId) {
            return $this->productDataRepository->getProductData($childrenId, $review->getStoreId());
        }

        return $productData;
    }

    protected function modifyNickname(\Magento\Review\Model\Review $review): string
    {
        $nickname = $review->getNickname();
        $nickname = $this->nicknameModifier->modify($nickname);

        return $this->filter($nickname);
    }

    public function filter(string $value): string
    {
        return $this->htmlEntities->filter($value);
    }
}
