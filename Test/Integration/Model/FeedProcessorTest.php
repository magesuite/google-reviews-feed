<?php
declare(strict_types=1);

namespace MageSuite\GoogleReviewsFeed\Model\Test\Integration\Model;

class FeedProcessorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var string
     */
    protected $mediaDirectory;

    /**
     * @var \MageSuite\GoogleReviewsFeed\Model\FeedProcessor
     */
    protected $feedProcessor;

    /**
     * @var \MageSuite\GoogleReviewsFeed\Model\Io
     */
    protected $io;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $date;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    protected function setUp(): void
    {
        $objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->mediaDirectory = $objectManager->get(\Magento\Framework\Filesystem::class)
            ->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $this->feedProcessor = $objectManager->get(\MageSuite\GoogleReviewsFeed\Model\FeedProcessor::class);
        $this->io = $objectManager->get(\MageSuite\GoogleReviewsFeed\Model\Io::class);
        $this->date = $objectManager->get(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::class);
        $this->registry = $objectManager->get(\Magento\Framework\Registry::class);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture default/reviews_feed/general/enabled 1
     * @magentoDataFixture MageSuite_GoogleReviewsFeed::Test/Integration/_files/product_review_with_rating.php
     */
    public function testIfGenerateProperXmlFile(): void
    {
        $reviewModel = $this->registry->registry('review_data');
        $reviewTimestamp = $this->date->date(strtotime($reviewModel->getCreatedAt()))->format('c');
        $this->feedProcessor->execute();

        $domDocument = new \DOMDocument();
        $domDocument->load($this->getFilePath());

        $publisher = $domDocument->getElementsByTagName('publisher')->item(0);
        $xpath = new \DOMXpath($domDocument);
        $expectedPublisher = [
            'name' => 'Sample Retailer',
            'favicon' => 'http://www.example.com/favicon.png'
        ];

        foreach ($expectedPublisher as $attribute => $attributeValue) {
            $query = $xpath->query($attribute, $publisher);
            $this->assertEquals($attributeValue, $query->item(0)->textContent);
        }

        $review = $domDocument->getElementsByTagName('review')->item(0);
        $expectedReview = [
            'review_id' => $reviewModel->getId(),
            'reviewer/name' => 'Nickname',
            'reviewer/reviewer_id' => 1,
            'review_timestamp' => $reviewTimestamp,
            'title' => 'Review Summary',
            'content' => 'Review text',
            'review_url' => 'http://localhost/index.php/simple-product.html',
            'ratings/overall' => 2,
            'products/product/product_ids/skus/sku' => 'simple',
            'products/product/product_ids/gtins/gtin' => '10000000',
            'products/product/product_ids/brands/brand' => 'Example Brand',
            'products/product/product_name' => 'Simple Product',
            'products/product/product_url' => 'http://localhost/index.php/simple-product.html'
        ];

        foreach ($expectedReview as $attribute => $attributeValue) {
            $query = $xpath->query($attribute, $review);
            $this->assertEquals($attributeValue, $query->item(0)->textContent);
        }
    }

    /**
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture default/reviews_feed/general/enabled 1
     * @magentoDataFixture MageSuite_GoogleReviewsFeed::Test/Integration/_files/grouped_product_review_with_rating.php
     */
    public function testIfGenerateProperXmlFileForGroupedProduct(): void
    {
        $reviewModel = $this->registry->registry('review_data_grouped');
        $reviewTimestamp = $this->date->date(strtotime($reviewModel->getCreatedAt()))->format('c');

        $this->feedProcessor->execute();
        $domDocument = new \DOMDocument();
        $domDocument->load($this->getFilePath());
        $review = $domDocument->getElementsByTagName('review')->item(0);
        $xpath = new \DOMXpath($domDocument);
        $expectedReview = [
            'review_id' => $reviewModel->getId(),
            'reviewer/name' => 'Nickname',
            'reviewer/reviewer_id' => 1,
            'review_timestamp' => $reviewTimestamp,
            'title' => 'Review Summary',
            'content' => 'Review text',
            'review_url' => 'http://localhost/index.php/grouped-product.html',
            'ratings/overall' => 2,
            'products/product/product_ids/skus/sku' => 'simple_11',
            'products/product/product_ids/gtins/gtin' => '10000000',
            'products/product/product_ids/brands/brand' => 'Example Brand',
            'products/product/product_name' => 'Simple 11',
            'products/product/product_url' => 'http://localhost/index.php/grouped-product.html'
        ];

        foreach ($expectedReview as $attribute => $attributeValue) {
            $query = $xpath->query($attribute, $review);
            $this->assertEquals($attributeValue, $query->item(0)->textContent);
        }
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture default/reviews_feed/general/enabled 1
     * @magentoDataFixture MageSuite_GoogleReviewsFeed::Test/Integration/_files/multiple_reviews.php
     */
    public function testIfGenerateProperXmlFileForMultipleReviews(): void
    {
        $this->feedProcessor->execute();
        $domDocument = new \DOMDocument();
        $domDocument->load($this->getFilePath());
        $expectedReviews = [
            [
                'reviewer/name' => 'Nickname',
                'title' => '1 filter second review',
                'content' => 'Review text',
                'ratings/overall' => '2',
                'review_url' => 'http://localhost/index.php/simple-product3.html',
                'products/product/product_ids/skus/sku' => 'simple3',
                'products/product/product_ids/gtins/gtin' => '10000000',
                'products/product/product_ids/brands/brand' => 'Example Brand',
                'products/product/product_name' => 'Simple Product3',
                'products/product/product_url' => 'http://localhost/index.php/simple-product3.html'
            ],
            [
                'reviewer/name' => 'Nickname',
                'title' => '2 filter first review',
                'content' => 'Review text',
                'ratings/overall' => '2',
                'review_url' => 'http://localhost/index.php/simple-product2.html',
                'products/product/product_ids/skus/sku' => 'simple2',
                'products/product/product_ids/gtins/gtin' => '10000000',
                'products/product/product_ids/brands/brand' => 'Example Brand',
                'products/product/product_name' => 'Simple Product2',
                'products/product/product_url' => 'http://localhost/index.php/simple-product2.html'
            ]
        ];

        foreach ($expectedReviews as $key => $expectedReview) {
            $review = $domDocument->getElementsByTagName('review')->item($key);
            $xpath = new \DOMXpath($domDocument);

            foreach ($expectedReview as $attribute => $attributeValue) {
                $query = $xpath->query($attribute, $review);
                $this->assertEquals($attributeValue, $query->item(0)->textContent);
            }
        }
    }

    protected function getFilePath(): string
    {
        return $this->mediaDirectory->getAbsolutePath() . $this->io->getFilePath();
    }

    protected function tearDown(): void
    {
        $this->mediaDirectory->delete($this->getFilePath());
    }
}
