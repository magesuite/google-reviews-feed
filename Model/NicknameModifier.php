<?php
declare(strict_types=1);

namespace MageSuite\GoogleReviewsFeed\Model;

class NicknameModifier
{
    public function modify(string $nickname): string
    {
        $nickname = trim($nickname);

        if (strpos($nickname, '@') !== false) {
            return '';
        }

        if (strpos($nickname, ' ') !== false) {
            list($firstname, $lastname) = explode(' ', $nickname, 2);
            $nickname = sprintf('%s %s.', $firstname, $lastname[0]);
        }

        return $nickname;
    }
}
