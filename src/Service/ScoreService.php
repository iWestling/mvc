<?php

namespace App\Service;

use Doctrine\Persistence\ManagerRegistry;
use App\Entity\GamePlayer;
use App\Entity\Scores;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use DateTime;

class ScoreService
{
    private ManagerRegistry $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function submitScore(string $username, int $age, int $scoreValue): JsonResponse
    {
        try {
            $entityManager = $this->doctrine->getManager();

            // Create new GamePlayer entity
            $player = new GamePlayer();
            $player->setUsername($username);
            $player->setAge($age);

            // Create new Scores entity
            $score = new Scores();
            $score->setUserId($player);  // Set the relationship between player and score
            $score->setScore($scoreValue);
            $score->setDate(new DateTime());  // Set the current date and time

            // Persist entities to the database
            $entityManager->persist($player);
            $entityManager->persist($score);
            $entityManager->flush();

            return new JsonResponse(['success' => 'Score submitted successfully!'], Response::HTTP_OK);

        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Failed to submit score.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
