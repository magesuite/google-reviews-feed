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
        $this->feedProcessor->execute();
        $filePath = $this->mediaDirectory->getAbsolutePath() . $this->io->getFilePath();
        $review = $this->registry->registry('review_data');
        $reviewTimestamp = $this->date->date(strtotime($review->getCreatedAt()))->format('c');

        $this->assertStringEqualsFile(
            $filePath,
            "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n".
            '<feed xmlns:vc="http://www.w3.org/2007/XMLSchema-versioning" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"'
            . ' xsi:noNamespaceSchemaLocation="http://www.google.com/shopping/reviews/schema/product/2.3/product_reviews.xsd">' .
            '<version>2.3</version><publisher><name>Sample Retailer</name><favicon>http://www.example.com/favicon.png</favicon></publisher>' .
            "<reviews><review><review_id>{$review->getId()}</review_id><reviewer><name><![CDATA[Nickname]]></name>" .
            "<reviewer_id>1</reviewer_id></reviewer><review_timestamp>{$reviewTimestamp}</review_timestamp>".
            '<title>Review Summary</title><content><![CDATA[Review text]]></content><review_url type="singleton">http://localhost/index.php/simple-product.html</review_url>'.
            '<ratings><overall min="1" max="5">2</overall></ratings>' .
            '<products><product><product_ids><skus><sku>simple</sku></skus></product_ids><product_name>Simple Product</product_name>' .
            "<product_url>http://localhost/index.php/simple-product.html</product_url></product></products></review></reviews></feed>\n"
        );
        $this->mediaDirectory->delete($filePath);
    }

    /**
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture default/reviews_feed/general/enabled 1
     * @magentoDataFixture MageSuite_GoogleReviewsFeed::Test/Integration/_files/grouped_product_review_with_rating.php
     */
    public function testIfGenerateProperXmlFileForGroupedProduct(): void
    {
        $this->feedProcessor->execute();
        $filePath = $this->mediaDirectory->getAbsolutePath() . $this->io->getFilePath();
        $review = $this->registry->registry('review_data_grouped');
        $reviewTimestamp = $this->date->date(strtotime($review->getCreatedAt()))->format('c');

        $this->assertStringEqualsFile(
            $filePath,
            "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n".
            '<feed xmlns:vc="http://www.w3.org/2007/XMLSchema-versioning" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"'
            . ' xsi:noNamespaceSchemaLocation="http://www.google.com/shopping/reviews/schema/product/2.3/product_reviews.xsd">' .
            '<version>2.3</version><publisher><name>Sample Retailer</name><favicon>http://www.example.com/favicon.png</favicon></publisher>' .
            "<reviews><review><review_id>{$review->getId()}</review_id><reviewer><name><![CDATA[Nickname]]></name>" .
            "<reviewer_id>1</reviewer_id></reviewer><review_timestamp>{$reviewTimestamp}</review_timestamp>".
            '<title>Review Summary</title><content><![CDATA[Review text]]></content><review_url type="singleton">http://localhost/index.php/grouped-product.html</review_url>'.
            '<ratings><overall min="1" max="5">2</overall></ratings>' .
            '<products><product><product_ids><skus><sku>simple_11</sku></skus></product_ids><product_name>Simple 11</product_name>' .
            "<product_url>http://localhost/index.php/grouped-product.html</product_url></product></products></review></reviews></feed>\n"
        );
        $this->mediaDirectory->delete($filePath);
    }
}
