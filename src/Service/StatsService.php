<?php

namespace App\Service;

class StatsService
{
    private string $file;

    public function __construct(string $projectDir)
    {
        $this->file = $projectDir . '/var/stats.json';
    }

    private function load(): array
    {
        if (!file_exists($this->file)) {
            return ['menus' => []];
        }

        return json_decode(file_get_contents($this->file), true);
    }

    private function save(array $data): void
    {
        file_put_contents($this->file, json_encode($data, JSON_PRETTY_PRINT));
    }

    /**
     * Met à jour les stats lorsqu'une commande est passée
     */
    public function updateMenuStats(int $menuId, float $montant): void
    {
        $stats = $this->load();

        if (!isset($stats['menus'][$menuId])) {
            $stats['menus'][$menuId] = [
                'commandes' => 0,
                'ca' => 0
            ];
        }

        $stats['menus'][$menuId]['commandes']++;
        $stats['menus'][$menuId]['ca'] += $montant;

        $this->save($stats);
    }

    /**
     * Récupère toutes les stats
     */
    public function getStats(): array
    {
        return $this->load();
    }
}
