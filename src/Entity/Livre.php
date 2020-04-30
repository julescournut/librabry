<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\LivreRepository")
 */
class Livre
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $titre;

    /**
     * @ORM\Column(type="float")
     */
    private $prix;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Auteur", inversedBy="livres")
     * @ORM\JoinColumn(nullable=false)
     */
    private $auteur;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Genre", inversedBy="livres")
     */
    private $genres;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $image;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Avis", mappedBy="livre", orphanRemoval=true)
     */
    private $avis;

    /**
     * @ORM\Column(type="text")
     */
    private $resume;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Saga", inversedBy="livres")
     */
    private $saga;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $volume;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Achat", mappedBy="livre")
     */
    private $achats;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\DetailLivraison", mappedBy="livre")
     */
    private $detailLivraisons;

    /**
     * @ORM\Column(type="integer")
     */
    private $stock;

    /**
     * @ORM\Column(type="boolean")
     */
    private $tendance;

    public function __construct()
    {
        $this->genres = new ArrayCollection();
        $this->avis = new ArrayCollection();
        $this->achats = new ArrayCollection();
        $this->detailLivraisons = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): self
    {
        $this->titre = $titre;

        return $this;
    }

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(float $prix): self
    {
        $this->prix = $prix;

        return $this;
    }

    public function getAuteur(): ?Auteur
    {
        return $this->auteur;
    }

    public function setAuteur(?Auteur $auteur): self
    {
        $this->auteur = $auteur;

        return $this;
    }

    /**
     * @return Collection|Genre[]
     */
    public function getGenres(): Collection
    {
        return $this->genres;
    }

    public function addGenre(Genre $genre): self
    {
        if (!$this->genres->contains($genre)) {
            $this->genres[] = $genre;
        }

        return $this;
    }

    public function removeGenre(Genre $genre): self
    {
        if ($this->genres->contains($genre)) {
            $this->genres->removeElement($genre);
        }

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): self
    {
        $this->image = $image;

        return $this;
    }

    /**
     * @return Collection|Avis[]
     */
    public function getAvis(): Collection
    {
        return $this->avis;
    }

    public function addAvi(Avis $avi): self
    {
        if (!$this->avis->contains($avi)) {
            $this->avis[] = $avi;
            $avi->setLivre($this);
        }

        return $this;
    }

    public function removeAvi(Avis $avi): self
    {
        if ($this->avis->contains($avi)) {
            $this->avis->removeElement($avi);
            // set the owning side to null (unless already changed)
            if ($avi->getLivre() === $this) {
                $avi->setLivre(null);
            }
        }

        return $this;
    }

    public function getResume(): ?string
    {
        return $this->resume;
    }

    public function setResume(string $resume): self
    {
        $this->resume = $resume;

        return $this;
    }

    public function getSaga(): ?saga
    {
        return $this->saga;
    }

    public function setSaga(?saga $saga): self
    {
        $this->saga = $saga;

        return $this;
    }

    public function getVolume(): ?int
    {
        return $this->volume;
    }

    public function setVolume(?int $volume): self
    {
        $this->volume = $volume;

        return $this;
    }

    /**
     * @return Collection|Achat[]
     */
    public function getAchats(): Collection
    {
        return $this->achats;
    }

    public function addAchat(Achat $achat): self
    {
        if (!$this->achats->contains($achat)) {
            $this->achats[] = $achat;
            $achat->setLivre($this);
        }

        return $this;
    }

    public function removeAchat(Achat $achat): self
    {
        if ($this->achats->contains($achat)) {
            $this->achats->removeElement($achat);
            // set the owning side to null (unless already changed)
            if ($achat->getLivre() === $this) {
                $achat->setLivre(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|DetailLivraison[]
     */
    public function getDetailLivraisons(): Collection
    {
        return $this->detailLivraisons;
    }

    public function addDetailLivraison(DetailLivraison $detailLivraison): self
    {
        if (!$this->detailLivraisons->contains($detailLivraison)) {
            $this->detailLivraisons[] = $detailLivraison;
            $detailLivraison->setLivre($this);
        }

        return $this;
    }

    public function removeDetailLivraison(DetailLivraison $detailLivraison): self
    {
        if ($this->detailLivraisons->contains($detailLivraison)) {
            $this->detailLivraisons->removeElement($detailLivraison);
            // set the owning side to null (unless already changed)
            if ($detailLivraison->getLivre() === $this) {
                $detailLivraison->setLivre(null);
            }
        }

        return $this;
    }

    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function setStock(int $stock): self
    {
        $this->stock = $stock;

        return $this;
    }

    public function getTendance(): ?bool
    {
        return $this->tendance;
    }

    public function setTendance(bool $tendance): self
    {
        $this->tendance = $tendance;

        return $this;
    }
}
