<?php

namespace App\Service\NoSQL;

use App\Document\AppLog;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Security\Core\User\UserInterface;

class AdminLogService
{
    public function __construct(
        private DocumentManager $dm
    ) {}

    public function log(UserInterface $admin, string $action, array $details = []): void
    {
        /** @var \App\Entity\Utilisateur $admin */

        $context = array_merge([
            'adminId' => $admin->getId(),
        ], $details);

        $log = new AppLog(
            'admin',
            $action,
            $context
        );

        $this->dm->persist($log);
        $this->dm->flush();
    }

    public function getRecentLogs(int $limit = 20): array
    {
        return $this->dm->getRepository(AppLog::class)
            ->findBy(
                ['type' => 'admin'],
                ['date' => 'DESC'],
                $limit
            );
    }
}
