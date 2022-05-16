<?php
declare(strict_types=1);

namespace MageSuite\GoogleReviewsFeed\Model\Config\Backend;

class Path extends \Magento\Framework\App\Config\Value
{
    public function beforeSave()
    {
        $value = $this->getValue();

        if ($value && preg_match('#\.\.[\\\/]#', $value)) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Please define a correct path.'));
        }

        return parent::beforeSave();
    }
}
