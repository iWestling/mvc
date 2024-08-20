<?php

namespace App\Entity;

use App\Repository\GamePlayerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GamePlayerRepository::class)]
class GamePlayer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $idn = null;

    #[ORM\Column(length: 255)]
    private ?string $username = null;

    #[ORM\Column]
    private ?int $age = null;

    /**
     * @var Collection<int, Scores>
     */
    #[ORM\OneToMany(targetEntity: Scores::class, mappedBy: 'user_id')]
    private Collection $scores;

    public function __construct()
    {
        $this->scores = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->idn;
    }

    public function setId(int $idn): static
    {
        $this->idn = $idn;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getAge(): ?int
    {
        return $this->age;
    }

    public function setAge(int $age): static
    {
        $this->age = $age;

        return $this;
    }

    /**
     * @return Collection<int, Scores>
     */
    public function getScores(): Collection
    {
        return $this->scores;
    }

    public function addScore(Scores $score): static
    {
        if (!$this->scores->contains($score)) {
            $this->scores->add($score);
            $score->setUserId($this);
        }

        return $this;
    }

    public function removeScore(Scores $score): static
    {
        if ($this->scores->removeElement($score)) {
            // set the owning side to null (unless already changed)
            if ($score->getUserId() === $this) {
                $score->setUserId(null);
            }
        }

        return $this;
    }
}
