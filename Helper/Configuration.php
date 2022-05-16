<?php
declare(strict_types=1);

namespace MageSuite\GoogleReviewsFeed\Helper;

class Configuration extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_GENERAL_ENABLED = 'reviews_feed/general/enabled';
    const XML_PATH_GENERAL_PUBLISHER_NAME = 'reviews_feed/general/publisher_name';
    const XML_PATH_GENERAL_PUBLISHER_FAVICON_URL = 'reviews_feed/general/publisher_favicon_url';
    const XML_PATH_FEED_FILENAME = 'reviews_feed/feed/filename';
    const XML_PATH_FEED_PATH = 'reviews_feed/feed/path';

    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_GENERAL_ENABLED);
    }

    public function getPublisherName(): string
    {
        return (string)$this->scopeConfig->getValue(self::XML_PATH_GENERAL_PUBLISHER_NAME);
    }

    public function getPublisherFaviconUrl(): string
    {
        return (string)$this->scopeConfig->getValue(self::XML_PATH_GENERAL_PUBLISHER_FAVICON_URL);
    }

    public function getFeedFilename(): string
    {
        return (string)$this->scopeConfig->getValue(self::XML_PATH_FEED_FILENAME);
    }

    public function getFeedPath(): string
    {
        return (string)$this->scopeConfig->getValue(self::XML_PATH_FEED_PATH);
    }
}
