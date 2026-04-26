<?php

namespace App\Service\NoSql;

class AllergeneService
{
    private string $file;

    public function __construct(string $kernelProjectDir)
    {
        $this->file = $kernelProjectDir . '/nosql/Allergenes.json';
    }

    private function load(): array
    {
        if (!file_exists($this->file)) {
            return [];
        }
        return json_decode(file_get_contents($this->file), true) ?? [];
    }

    private function save(array $data): void
    {
        file_put_contents($this->file, json_encode($data, JSON_PRETTY_PRINT));
    }

    public function getAllergenesForMenu(int $menuId): array
    {
        $data = $this->load();
        foreach ($data as $doc) {
            if ($doc['menuId'] === $menuId) {
                return $doc['allergenes'];
            }
        }
        return [];
    }

    public function setAllergenesForMenu(int $menuId, array $allergenes): void
    {
        $data = $this->load();

        // Supprimer l'ancien document
        $data = array_filter($data, fn($d) => $d['menuId'] !== $menuId);

        // Ajouter le nouveau
        $data[] = [
            'menuId' => $menuId,
            'allergenes' => $allergenes
        ];

        $this->save($data);
    }
}
