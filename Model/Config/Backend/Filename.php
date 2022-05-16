<?php
declare(strict_types=1);

namespace MageSuite\GoogleReviewsFeed\Model\Config\Backend;

class Filename extends \Magento\Framework\App\Config\Value
{
    public function beforeSave()
    {
        $value = $this->getValue();

        if (!preg_match('#^[a-zA-Z0-9_\.]+$#', $value)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __(
                    'Please use only letters (a-z or A-Z), numbers (0-9) or underscores (_) in the filename.'
                    . ' No spaces or other characters are allowed.'
                )
            );
        }

        return parent::beforeSave();
    }
}
