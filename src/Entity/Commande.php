<?php

namespace App\Entity;

use App\Repository\CommandeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommandeRepository::class)]
class Commande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(length: 50)]
    private string $status = 'en_attente';

    #[ORM\Column(type: 'float')]
    private float $total = 0;

    #[ORM\OneToMany(
        targetEntity: CommandeItem::class,
        mappedBy: 'commande',
        cascade: ['persist'],
        orphanRemoval: true
    )]
    private Collection $items;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: 'commandes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $utilisateur = null;

    // 🔵 AJOUT : relation inverse pour Avis (OneToOne)
    #[ORM\OneToOne(mappedBy: 'commande', cascade: ['persist', 'remove'])]
    private ?Avis $avis = null;

    // 🔵 AJOUT : relation inverse pour CommandeStatut (OneToMany)
    #[ORM\OneToMany(targetEntity: CommandeStatut::class, mappedBy: 'commande', cascade: ['persist', 'remove'])]
    private Collection $commandeStatuts;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->items = new ArrayCollection();
        $this->commandeStatuts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getTotal(): float
    {
        return $this->total;
    }

    public function setTotal(float $total): self
    {
        $this->total = $total;
        return $this;
    }

    /** ITEMS **/
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(CommandeItem $item): self
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setCommande($this);
        }
        return $this;
    }

    public function removeItem(CommandeItem $item): self
    {
        if ($this->items->removeElement($item)) {
            if ($item->getCommande() === $this) {
                $item->setCommande(null);
            }
        }
        return $this;
    }

    /** UTILISATEUR **/
    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?Utilisateur $utilisateur): self
    {
        $this->utilisateur = $utilisateur;
        return $this;
    }

    /** AVIS **/
    public function getAvis(): ?Avis
    {
        return $this->avis;
    }

    public function setAvis(?Avis $avis): self
    {
        if ($avis !== null && $avis->getCommande() !== $this) {
            $avis->setCommande($this);
        }

        $this->avis = $avis;
        return $this;
    }

    /** COMMANDE STATUTS **/
    public function getCommandeStatuts(): Collection
    {
        return $this->commandeStatuts;
    }

    public function addCommandeStatut(CommandeStatut $commandeStatut): self
    {
        if (!$this->commandeStatuts->contains($commandeStatut)) {
            $this->commandeStatuts->add($commandeStatut);
            $commandeStatut->setCommande($this);
        }
        return $this;
    }

    public function removeCommandeStatut(CommandeStatut $commandeStatut): self
    {
        if ($this->commandeStatuts->removeElement($commandeStatut)) {
            if ($commandeStatut->getCommande() === $this) {
                $commandeStatut->setCommande(null);
            }
        }
        return $this;
    }
}
