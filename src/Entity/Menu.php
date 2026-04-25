<?php

namespace App\Entity;

use App\Entity\Avis;
use App\Repository\MenuRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MenuRepository::class)]
class Menu
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    private ?string $titre = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column]
    private ?int $nbPersonnesMin = null;

    #[ORM\Column]
    private ?float $prixBase = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $conditions = null;

    #[ORM\Column]
    private ?int $stockDisponible = null;

    #[ORM\ManyToOne(inversedBy: 'menus')]
    private ?Theme $theme = null;

    #[ORM\ManyToOne(inversedBy: 'menus')]
    private ?Regime $regime = null;



    // Plusieurs images (stockées en JSON)
    #[ORM\Column(type: 'json')]
    private array $images = [];

    // Image principale (pour affichage rapide)
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;



    /**
     * @var Collection<int, Plat>
     */
    #[ORM\ManyToMany(targetEntity: Plat::class, inversedBy: 'menus')]
    private Collection $plats;

    #[ORM\OneToMany(mappedBy: 'menu', targetEntity: Avis::class, cascade: ['remove'])]
    private Collection $avis;




    public function __construct()
    {
        $this->plats = new ArrayCollection();
        $this->images = [];
        $this->avis = new ArrayCollection();

    }

    // -------------------------
    // Getters / Setters
    // -------------------------

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getNbPersonnesMin(): ?int
    {
        return $this->nbPersonnesMin;
    }

    public function setNbPersonnesMin(int $nbPersonnesMin): static
    {
        $this->nbPersonnesMin = $nbPersonnesMin;
        return $this;
    }

    public function getPrixBase(): ?float
    {
        return $this->prixBase;
    }

    public function setPrixBase(float $prixBase): static
    {
        $this->prixBase = $prixBase;
        return $this;
    }

    public function getConditions(): ?string
    {
        return $this->conditions;
    }

    public function setConditions(string $conditions): static
    {
        $this->conditions = $conditions;
        return $this;
    }

    public function getStockDisponible(): ?int
    {
        return $this->stockDisponible;
    }

    public function setStockDisponible(int $stockDisponible): static
    {
        $this->stockDisponible = $stockDisponible;
        return $this;
    }

    public function getTheme(): ?Theme
    {
        return $this->theme;
    }

    public function setTheme(?Theme $theme): static
    {
        $this->theme = $theme;
        return $this;
    }

    public function getRegime(): ?Regime
    {
        return $this->regime;
    }

    public function setRegime(?Regime $regime): static
    {
        $this->regime = $regime;
        return $this;
    }

    // -------------------------
    // Plats
    // -------------------------

    /**
     * @return Collection<int, Plat>
     */
    public function getPlats(): Collection
    {
        return $this->plats;
    }

    public function addPlat(Plat $plat): static
    {
        if (!$this->plats->contains($plat)) {
            $this->plats->add($plat);
        }
        return $this;
    }

    public function removePlat(Plat $plat): static
    {
        $this->plats->removeElement($plat);
        return $this;
    }

    // -------------------------
    // Images multiples
    // -------------------------

    public function getImages(): array
    {
        return $this->images;
    }

    public function setImages(array $images): static
    {
        $this->images = $images;
        return $this;
    }

    // -------------------------
    // Image principale
    // -------------------------

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;
        return $this;
    }

    public function getPrixParPersonne(): float
{
    return $this->prixBase / $this->nbPersonnesMin;
}

/**
 * @return Collection<int, Avis>
 */
public function getAvis(): Collection
{
    return $this->avis;
}

public function addAvi(Avis $avi): static
{
    if (!$this->avis->contains($avi)) {
        $this->avis->add($avi);
        $avi->setMenu($this);
    }
    return $this;
}

public function removeAvi(Avis $avi): static
{
    if ($this->avis->removeElement($avi)) {
        if ($avi->getMenu() === $this) {
            $avi->setMenu(null);
        }
    }
    return $this;
}


}