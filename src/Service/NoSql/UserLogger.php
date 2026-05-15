<?php

namespace App\Service\NoSQL;

use App\Document\UserLog;
use Doctrine\ODM\MongoDB\DocumentManager;

class UserLogger
{
    public function __construct(
        private DocumentManager $dm
    ) {}

    public function log(int $userId, string $action, array $details = []): void
    {
        $log = new UserLog(
            $userId,
            $action,
            $details
        );

        $this->dm->persist($log);
        $this->dm->flush();
    }
}
