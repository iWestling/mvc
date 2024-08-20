<?php

namespace App\Entity;

use App\Repository\ScoresRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ScoresRepository::class)]
class Scores
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $idn = null;

    #[ORM\ManyToOne(inversedBy: 'scores')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'idn', nullable: false)] // Specify the referenced column
    private ?GamePlayer $userId = null;

    #[ORM\Column]
    private ?int $score = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date = null;

    public function getId(): ?int
    {
        return $this->idn;
    }

    public function getUserId(): ?GamePlayer
    {
        return $this->userId;
    }

    public function setUserId(?GamePlayer $userId): static
    {
        $this->userId = $userId;

        return $this;
    }

    public function getScore(): ?int
    {
        return $this->score;
    }

    public function setScore(int $score): static
    {
        $this->score = $score;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }
}
