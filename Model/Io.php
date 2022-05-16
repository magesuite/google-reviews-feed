<?php
declare(strict_types=1);

namespace MageSuite\GoogleReviewsFeed\Model;

class Io
{
    protected \Magento\Framework\Filesystem $filesystem;

    protected \MageSuite\GoogleReviewsFeed\Helper\Configuration $configuration;

    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \MageSuite\GoogleReviewsFeed\Helper\Configuration $configuration
    ) {
        $this->filesystem = $filesystem;
        $this->configuration = $configuration;
    }

    public function saveFile(string $data): void
    {
        $mediaDirectory = $this->filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $mediaDirectory->writeFile($this->getFilePath(), $data);
    }

    public function getFilePath(): string
    {
        return $this->configuration->getFeedPath() . $this->configuration->getFeedFilename();
    }
}
