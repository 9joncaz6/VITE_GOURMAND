<?php

namespace App\Service;

use App\Document\AppLog;
use Doctrine\ODM\MongoDB\DocumentManager;

class AppLogger
{
    public function __construct(private DocumentManager $dm) {}

    public function log(string $type, string $message, array $context = []): void
    {
        $log = new AppLog($type, $message, $context);
        $this->dm->persist($log);
        $this->dm->flush();
    }
}