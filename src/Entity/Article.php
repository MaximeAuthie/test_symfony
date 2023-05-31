<?php

namespace App\Entity;

use App\Repository\ArticleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups; //nécessaire pour définir les groupes pour les API (à ajouter)

#[ORM\Entity(repositoryClass: ArticleRepository::class)]
class Article
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['article:readAll', 'article:readById'])] //nécessaire pour les API => ici on créé 2 groupes, un pour chaque API (parce qu'ils ne renvoient pas les mêmes informations) 
    private ?int $id = null;

    #[ORM\Column(length: 50)]

    #[Groups(['article:readAll', 'article:readById'])] 
    private ?string $titre = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['article:readAll', 'article:readById'])] 
    private ?string $contenu = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups('article:readAll')] //nécessaire pour les API (à ajouter)
    private ?\DateTimeInterface $date = null;

    #[ORM\ManyToMany(targetEntity: Category::class, inversedBy: 'articles')]
    #[Groups('article:readAll')] //nécessaire pour les API (à ajouter) => étant donné que c'est un obje contruit à partir d'une classe, il faut préciser dans la classe les champs à afficher
    private Collection $categories;

    #[ORM\ManyToOne(inversedBy: 'articles')]
    #[Groups('article:readAll')] //nécessaire pour les API (à ajouter) => étant donné que c'est un obje contruit à partir d'une classe, il faut préciser dans la classe les champs à afficher
    private ?User $User = null;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
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

    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    public function setContenu(string $contenu): self
    {
        $this->contenu = $contenu;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return Collection<int, Category>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): self
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
        }

        return $this;
    }

    public function removeCategory(Category $category): self
    {
        $this->categories->removeElement($category);

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->User;
    }

    public function setUser(?User $User): self
    {
        $this->User = $User;

        return $this;
    }
}
