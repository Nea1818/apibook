<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GenreRepository")
 * @ApiResource(
 *       itemOperations={
 *           "get_simple" = {
 *                "method"= "GET",
 *                 "path"="/genres/{id}/simple",
 *                 "normalization_context"={"groups"={"listeGenreSimple"}}
 *                      },
 *            "get_full" = {
 *                "method"= "GET",
 *                 "path"="/genres/{id}/full",
 *                 "normalization_context"={"groups"={"listeGenreFull"}}
 *                      }
 * },
 *        collectionOperations={"get"}
 *
 * )
 * @UniqueEntity(
 * fields={"libelle"},
 *     message="Ce libellé existe déjà.")
 */
class Genre
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"listGenreSimple","listGenreFull"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"listGenreSimple","listGenreFull"})
     * @Assert\Length(
     *  min=2,
     *  max=50,
     *  minMessage="Le libellé doit contenir au moins {{ limit }} caractères",
     *  maxMessage="Le libellé doit contenir au plus {{ limit }} caractères",)
     */
    private $libelle;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Livre", mappedBy="genre")
     * @Groups({"listGenreFull"})
     * @ApiSubresource
     */
    private $livres;

    public function __construct()
    {
        $this->livres = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): self
    {
        $this->libelle = $libelle;

        return $this;
    }

    /**
     * @return Collection|Livre[]
     */
    public function getLivres(): Collection
    {
        return $this->livres;
    }

    public function addLivre(Livre $livre): self
    {
        if (!$this->livres->contains($livre)) {
            $this->livres[] = $livre;
            $livre->setGenre($this);
        }

        return $this;
    }

    public function removeLivre(Livre $livre): self
    {
        if ($this->livres->contains($livre)) {
            $this->livres->removeElement($livre);
            // set the owning side to null (unless already changed)
            if ($livre->getGenre() === $this) {
                $livre->setGenre(null);
            }
        }

        return $this;
    }
    
    public function __toString()
    {
        return (string)$this->libelle;
    }
}
