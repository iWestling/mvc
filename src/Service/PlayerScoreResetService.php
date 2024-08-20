<?php

namespace App\Service;

use App\Entity\GamePlayer;
use App\Entity\Scores;
use App\Repository\GamePlayerRepository;
use App\Repository\ScoresRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Exception;
use DateTime;

class PlayerScoreResetService
{
    private GamePlayerRepository $gamePlayerRepository;
    private ScoresRepository $scoresRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(GamePlayerRepository $gamePlayerRepository, ScoresRepository $scoresRepository, EntityManagerInterface $entityManager)
    {
        $this->gamePlayerRepository = $gamePlayerRepository;
        $this->scoresRepository = $scoresRepository;
        $this->entityManager = $entityManager;
    }

    public function resetPlayerData(): Response
    {
        // Load original data
        $playersData = [
            ['username' => 'Test Player 1', 'age' => 43],
            ['username' => 'Test Player 2', 'age' => 21],
            ['username' => 'Test Player 3', 'age' => 55]
        ];

        $scoresData = [
            ['score' => 100, 'date' => new DateTime('2024-01-01')],
            ['score' => 50, 'date' => new DateTime('2024-02-01')],
            ['score' => 200, 'date' => new DateTime('2024-03-01')]
        ];

        try {
            // Clear existing data
            $this->scoresRepository->deleteAll();
            $this->gamePlayerRepository->deleteAll();

            // Insert original data
            foreach ($playersData as $index => $playerData) {
                $player = new GamePlayer();
                $player->setUsername($playerData['username']);
                $player->setAge($playerData['age']);

                // Persist the player entity
                $this->entityManager->persist($player);

                // Assign score to the player
                $score = new Scores();
                $score->setUserId($player); // Set the relationship
                $score->setScore($scoresData[$index]['score']);
                $score->setDate($scoresData[$index]['date']);

                // Persist the score entity
                $this->entityManager->persist($score);
            }

            // Flush changes to the database
            $this->entityManager->flush(); // Saves all changes to the database

        } catch (Exception $e) {
            return new Response('Error resetting player and score data: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new Response('Player and score database reset successful');
    }
}
