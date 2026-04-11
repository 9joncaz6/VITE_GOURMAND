<?php

namespace App\Service;

use App\Document\SearchHistory;
use Doctrine\ODM\MongoDB\DocumentManager;

class SearchTracker
{
    public function __construct(private DocumentManager $dm) {}

    public function track(int $userId, string $query): void
    {
        $entry = new SearchHistory($userId, $query);
        $this->dm->persist($entry);
        $this->dm->flush();
    }
}