<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\PointDeVenteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PointDeVenteRepository::class)]
#[ApiResource]
class PointDeVente
{
    #[Groups(['pointDeVente:collection'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['pointDeVente:collection'])]
    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\OneToMany(mappedBy: 'pointDeVente', targetEntity: Client::class)]
    private Collection $clients;

    #[ORM\OneToMany(mappedBy: 'pointDeVente', targetEntity: Vente::class)]
    private Collection $ventes;

    #[ORM\OneToMany(mappedBy: 'pointDeVente', targetEntity: RepportANouveau::class)]
    private Collection $repportANouveaus;

    #[ORM\OneToMany(mappedBy: 'pointDeVente', targetEntity: Approvisionnement::class)]
    private Collection $approvisionnements;

    #[ORM\OneToMany(mappedBy: 'pointDeVente', targetEntity: Utilisateur::class)]
    private Collection $utilisateurs;

    #[ORM\OneToMany(mappedBy: 'pointDeVente', targetEntity: Produit::class)]
    private Collection $produits;

    #[ORM\OneToMany(mappedBy: 'pointDeVente', targetEntity: Decaissement::class)]
    private Collection $decaissements;

    #[ORM\OneToMany(mappedBy: 'pointDeVente', targetEntity: RepportCaisse::class)]
    private Collection $repportCaisses;

    #[ORM\OneToMany(mappedBy: 'pointDeVente', targetEntity: Charge::class)]
    private Collection $charges;

    public function __construct()
    {
        $this->clients = new ArrayCollection();
        $this->ventes = new ArrayCollection();
        $this->repportANouveaus = new ArrayCollection();
        $this->approvisionnements = new ArrayCollection();
        $this->utilisateurs = new ArrayCollection();
        $this->produits = new ArrayCollection();
        $this->decaissements = new ArrayCollection();
        $this->repportCaisses = new ArrayCollection();
        $this->charges = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * @return Collection<int, Client>
     */
    public function getClients(): Collection
    {
        return $this->clients;
    }

    public function addClient(Client $client): static
    {
        if (!$this->clients->contains($client)) {
            $this->clients->add($client);
            $client->setPointDeVente($this);
        }

        return $this;
    }

    public function removeClient(Client $client): static
    {
        if ($this->clients->removeElement($client)) {
            // set the owning side to null (unless already changed)
            if ($client->getPointDeVente() === $this) {
                $client->setPointDeVente(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Vente>
     */
    public function getVentes(): Collection
    {
        return $this->ventes;
    }

    public function addVente(Vente $vente): static
    {
        if (!$this->ventes->contains($vente)) {
            $this->ventes->add($vente);
            $vente->setPointDeVente($this);
        }

        return $this;
    }

    public function removeVente(Vente $vente): static
    {
        if ($this->ventes->removeElement($vente)) {
            // set the owning side to null (unless already changed)
            if ($vente->getPointDeVente() === $this) {
                $vente->setPointDeVente(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, RepportANouveau>
     */
    public function getRepportANouveaus(): Collection
    {
        return $this->repportANouveaus;
    }

    public function addRepportANouveau(RepportANouveau $repportANouveau): static
    {
        if (!$this->repportANouveaus->contains($repportANouveau)) {
            $this->repportANouveaus->add($repportANouveau);
            $repportANouveau->setPointDeVente($this);
        }

        return $this;
    }

    public function removeRepportANouveau(RepportANouveau $repportANouveau): static
    {
        if ($this->repportANouveaus->removeElement($repportANouveau)) {
            // set the owning side to null (unless already changed)
            if ($repportANouveau->getPointDeVente() === $this) {
                $repportANouveau->setPointDeVente(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Approvisionnement>
     */
    public function getApprovisionnements(): Collection
    {
        return $this->approvisionnements;
    }

    public function addApprovisionnement(Approvisionnement $approvisionnement): static
    {
        if (!$this->approvisionnements->contains($approvisionnement)) {
            $this->approvisionnements->add($approvisionnement);
            $approvisionnement->setPointDeVente($this);
        }

        return $this;
    }

    public function removeApprovisionnement(Approvisionnement $approvisionnement): static
    {
        if ($this->approvisionnements->removeElement($approvisionnement)) {
            // set the owning side to null (unless already changed)
            if ($approvisionnement->getPointDeVente() === $this) {
                $approvisionnement->setPointDeVente(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Utilisateur>
     */
    public function getUtilisateurs(): Collection
    {
        return $this->utilisateurs;
    }

    public function addUtilisateur(Utilisateur $utilisateur): static
    {
        if (!$this->utilisateurs->contains($utilisateur)) {
            $this->utilisateurs->add($utilisateur);
            $utilisateur->setPointDeVente($this);
        }

        return $this;
    }

    public function removeUtilisateur(Utilisateur $utilisateur): static
    {
        if ($this->utilisateurs->removeElement($utilisateur)) {
            // set the owning side to null (unless already changed)
            if ($utilisateur->getPointDeVente() === $this) {
                $utilisateur->setPointDeVente(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Produit>
     */
    public function getProduits(): Collection
    {
        return $this->produits;
    }

    public function addProduit(Produit $produit): static
    {
        if (!$this->produits->contains($produit)) {
            $this->produits->add($produit);
            $produit->setPointDeVente($this);
        }

        return $this;
    }

    public function removeProduit(Produit $produit): static
    {
        if ($this->produits->removeElement($produit)) {
            // set the owning side to null (unless already changed)
            if ($produit->getPointDeVente() === $this) {
                $produit->setPointDeVente(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Decaissement>
     */
    public function getDecaissements(): Collection
    {
        return $this->decaissements;
    }

    public function addDecaissement(Decaissement $decaissement): static
    {
        if (!$this->decaissements->contains($decaissement)) {
            $this->decaissements->add($decaissement);
            $decaissement->setPointDeVente($this);
        }

        return $this;
    }

    public function removeDecaissement(Decaissement $decaissement): static
    {
        if ($this->decaissements->removeElement($decaissement)) {
            // set the owning side to null (unless already changed)
            if ($decaissement->getPointDeVente() === $this) {
                $decaissement->setPointDeVente(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, RepportCaisse>
     */
    public function getRepportCaisses(): Collection
    {
        return $this->repportCaisses;
    }

    public function addRepportCaiss(RepportCaisse $repportCaiss): static
    {
        if (!$this->repportCaisses->contains($repportCaiss)) {
            $this->repportCaisses->add($repportCaiss);
            $repportCaiss->setPointDeVente($this);
        }

        return $this;
    }

    public function removeRepportCaiss(RepportCaisse $repportCaiss): static
    {
        if ($this->repportCaisses->removeElement($repportCaiss)) {
            // set the owning side to null (unless already changed)
            if ($repportCaiss->getPointDeVente() === $this) {
                $repportCaiss->setPointDeVente(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Charge>
     */
    public function getCharges(): Collection
    {
        return $this->charges;
    }

    public function addCharge(Charge $charge): static
    {
        if (!$this->charges->contains($charge)) {
            $this->charges->add($charge);
            $charge->setPointDeVente($this);
        }

        return $this;
    }

    public function removeCharge(Charge $charge): static
    {
        if ($this->charges->removeElement($charge)) {
            // set the owning side to null (unless already changed)
            if ($charge->getPointDeVente() === $this) {
                $charge->setPointDeVente(null);
            }
        }

        return $this;
    }
}
