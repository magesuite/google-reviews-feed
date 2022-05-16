<?php
declare(strict_types=1);

namespace MageSuite\GoogleReviewsFeed\Cron;

class GenerateFeed
{
    protected \MageSuite\GoogleReviewsFeed\Helper\Configuration $configuration;

    protected \MageSuite\GoogleReviewsFeed\Model\FeedProcessor $feedProcessor;

    public function __construct(
        \MageSuite\GoogleReviewsFeed\Helper\Configuration $configuration,
        \MageSuite\GoogleReviewsFeed\Model\FeedProcessor $feedProcessor
    ) {
        $this->configuration = $configuration;
        $this->feedProcessor = $feedProcessor;
    }

    public function execute(): void
    {
        if (!$this->configuration->isEnabled()) {
            return;
        }

        $this->feedProcessor->execute();
    }
}
