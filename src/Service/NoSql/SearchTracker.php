<?php

namespace App\Service\NoSQL;

use App\Document\SearchHistory;
use Doctrine\ODM\MongoDB\DocumentManager;

class SearchTracker
{
    private DocumentManager $dm;

    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    /**
     * Tracking générique (recherche, action, page, etc.)
     */
    public function track(?int $userId, string $query = '', ?string $page = null): void
    {
        $query = trim($query);
        if ($query === '') {
            return;
        }

        $entry = new SearchHistory(
            $userId ?? 0,
            mb_strtolower($query),
            $page
        );

        $this->dm->persist($entry);
        $this->dm->flush();
    }

    /**
     * Top recherches / actions
     */
    public function getTopSearches(int $limit = 10): array
    {
        $collection = $this->dm->getDocumentCollection(SearchHistory::class);

        $pipeline = [
            ['$group' => ['_id' => '$query', 'count' => ['$sum' => 1]]],
            ['$sort'  => ['count' => -1]],
            ['$limit' => $limit],
        ];

        return iterator_to_array($collection->aggregate($pipeline));
    }

    /**
     * Historique d’un utilisateur
     */
    public function getSearchesByUser(int $userId): array
    {
        return $this->dm->getRepository(SearchHistory::class)
            ->findBy(['userId' => $userId], ['date' => 'DESC']);
    }
}
