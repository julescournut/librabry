<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DetailLivraisonRepository")
 */
class DetailLivraison
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Livre", inversedBy="detailLivraisons")
     * @ORM\JoinColumn(nullable=false)
     */
    private $livre;

    /**
     * @ORM\Column(type="integer")
     */
    private $quantite;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Livraison", inversedBy="detailLivraisons")
     * @ORM\JoinColumn(nullable=false)
     */
    private $livraison;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLivre(): ?livre
    {
        return $this->livre;
    }

    public function setLivre(?livre $livre): self
    {
        $this->livre = $livre;

        return $this;
    }

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): self
    {
        $this->quantite = $quantite;

        return $this;
    }

    public function getLivraison(): ?livraison
    {
        return $this->livraison;
    }

    public function setLivraison(?livraison $livraison): self
    {
        $this->livraison = $livraison;

        return $this;
    }
}
