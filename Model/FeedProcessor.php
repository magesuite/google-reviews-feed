<?php
declare(strict_types=1);

namespace MageSuite\GoogleReviewsFeed\Model;

class FeedProcessor
{
    protected \MageSuite\GoogleReviewsFeed\Model\Xml $xmlContent;
    
    protected \MageSuite\GoogleReviewsFeed\Model\Io $io;
    
    protected \Psr\Log\LoggerInterface $logger;
    
    public function __construct(
        \MageSuite\GoogleReviewsFeed\Model\Xml $xmlContent,
        \MageSuite\GoogleReviewsFeed\Model\Io $io,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->xmlContent = $xmlContent;
        $this->io = $io;
        $this->logger = $logger;
    }

    public function execute(): void
    {
        try {
            $xmlContent = $this->xmlContent->execute();
            $this->io->saveFile($xmlContent);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw $e;
        }
    }
}
