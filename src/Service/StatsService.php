<?php

namespace App\Service;

use App\Entity\Commande;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class StatsService
{
    private string $projectDir;

    public function __construct(ParameterBagInterface $params)
    {
        $this->projectDir = $params->get('kernel.project_dir');
    }

    private function loadData(): array
    {
        $path = $this->projectDir . '/var/stats/stats.json';

        if (!file_exists($path)) {
            return [
                'caTotal' => 0,
                'totalCommandes' => 0,
                'commandesParMenu' => [],
                'caParMenu' => []
            ];
        }

        return json_decode(file_get_contents($path), true);
    }

    private function saveData(array $data): void
    {
        $path = $this->projectDir . '/var/stats/stats.json';
        file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT));
    }

    /**
     * Mise à jour complète des statistiques après une commande
     */
    public function updateStats(Commande $commande): void
    {
        $data = $this->loadData();

        // 1) CA total
        $data['caTotal'] += $commande->getTotal();

        // 2) Nombre total de commandes
        $data['totalCommandes'] += 1;

        // 3) Stats par menu
        foreach ($commande->getItems() as $item) {
            $menuId = $item->getMenu()->getId();
            $qte = $item->getQuantite();
            $totalMenu = $item->getTotal();

            // Commandes par menu
            if (!isset($data['commandesParMenu'][$menuId])) {
                $data['commandesParMenu'][$menuId] = 0;
            }
            $data['commandesParMenu'][$menuId] += $qte;

            // CA par menu
            if (!isset($data['caParMenu'][$menuId])) {
                $data['caParMenu'][$menuId] = 0;
            }
            $data['caParMenu'][$menuId] += $totalMenu;
        }

        // 4) Sauvegarde
        $this->saveData($data);
    }

    public function getStats(): array
    {
        $data = $this->loadData();

        $caTotal = $data['caTotal'] ?? 0;
        $totalCommandes = $data['totalCommandes'] ?? 0;

        $panierMoyen = $totalCommandes > 0
            ? round($caTotal / $totalCommandes, 2)
            : 0;

        return [
            'caTotal' => $caTotal,
            'totalCommandes' => $totalCommandes,
            'panierMoyen' => $panierMoyen,
        ];
    }

    public function getCommandesParMenu(): array
    {
        return $this->loadData()['commandesParMenu'] ?? [];
    }

    public function getCaParMenu(): array
    {
        return $this->loadData()['caParMenu'] ?? [];
    }
}
