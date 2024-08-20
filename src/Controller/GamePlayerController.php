<?php

namespace App\Controller;

use App\Repository\ScoresRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

use App\Entity\GamePlayer;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Scores;
use App\Repository\GamePlayerRepository;
use App\Service\PlayerScoreResetService;

class GamePlayerController extends AbstractController
{
    private PlayerScoreResetService $resetService;

    public function __construct(PlayerScoreResetService $resetService)
    {
        $this->resetService = $resetService;
    }

    #[Route('/proj/about/database', name: 'game_database')]
    public function index(): Response
    {
        return $this->render('texas/database.html.twig', [
            'controller_name' => 'GamePlayerController',
        ]);
    }

    #[Route('/proj/about/database/gameplayers', name: 'game_players')]
    public function showAllGamePlayers(GamePlayerRepository $gamePlayerRepository): Response
    {
        $gamePlayers = $gamePlayerRepository->findAll();
        $gamePlayerData = [];

        foreach ($gamePlayers as $gamePlayer) {
            $gamePlayerData[] = [
                'id' => $gamePlayer->getId(),
                'username' => $gamePlayer->getUserName(),
                'age' => $gamePlayer->getAge(),
            ];
        }

        return $this->render('texas/gameplayer.html.twig', [
            'gameplayer' => $gamePlayerData,
        ]);
    }

    #[Route('/proj/about/database/highscores', name: 'highscores')]
    public function showAllScoresWithPlayers(ScoresRepository $scoresRepository): Response
    {
        // Fetch the joined data ordered by score
        $scoresWithPlayers = $scoresRepository->findAllWithPlayersOrderedByScore();

        // Pass the data to the Twig template
        return $this->render('texas/highscores.html.twig', [
            'scores_with_players' => $scoresWithPlayers,
        ]);
    }

    #[Route('/proj/about/database/reset-database', name: 'reset_database')]
    public function resetPlayerDatabase(): Response
    {
        return $this->resetService->resetPlayerData();
    }


    // #[Route('/proj/about/database/scores', name: 'scores')]
    // public function showAllScores(ScoresRepository $scoresRepository): Response
    // {
    //     $scores = $scoresRepository->findAll();
    //     $scoresData = [];

    //     foreach ($scores as $score) {
    //         $scoresData[] = [
    //             'id' => $score->getId(),
    //             'score_id' => $score->getScoreId(),
    //             'user_id' => $score->getUserId(),
    //             'score' => $score->getScore(),
    //             'date' => $score->getScore(),
    //         ];
    //     }

    //     return $this->render('texas/scores.html.twig', [
    //         'score' => $scoresData,
    //     ]);
    // }

    // #[Route('/proj/about/database/highscore', name: 'highscore_list')]
    // public function showAllHighScores(GamePlayerRepository $gamePlayerRepository): Response
    // {
    //     $highscores = $gamePlayerRepository->findAll();
    //     $highscoreData = [];

    //     foreach ($highscores as $highscore) {
    //         $highscoreData[] = [
    //             'id' => $highscore->getId(),
    //             'username' => $highscore->getUserName(),
    //             'age' => $highscore->getAge(),
    //         ];
    //     }

    //     return $this->render('texas/highscorelist.html.twig', [
    //         'highscores' => $highscoreData,
    //     ]);
    // }
}
