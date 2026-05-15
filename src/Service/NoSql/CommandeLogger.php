<?php

namespace App\Service\NoSQL;

use App\Document\CommandeLog;
use Doctrine\ODM\MongoDB\DocumentManager;

class CommandeLogger
{
    public function __construct(
        private DocumentManager $dm
    ) {}

    public function log(int $commandeId, string $action, array $metadata = []): void
    {
        $log = new CommandeLog(
            $commandeId,
            $action,
            $metadata
        );

        $this->dm->persist($log);
        $this->dm->flush();
    }
}
