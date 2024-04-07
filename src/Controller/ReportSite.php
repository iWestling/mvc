<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ReportSite extends AbstractController
{
    #[Route("/lucky", name: "lucky_number")]
    public function number(): Response
    {
        $number = random_int(0, 100);
        $imageNames = ['lucky1.jpg', 'lucky2.jpg', 'lucky3.jpg'];
        $randomImage = $imageNames[array_rand($imageNames)];

        $data = [
            'number' => $number,
            'image' => $randomImage
        ];

        return $this->render('lucky_number.html.twig', $data);
    }

    #[Route("/", name: "home")]
    public function home(): Response
    {
        return $this->render('home.html.twig');
    }

    #[Route("/about", name: "about")]
    public function about(): Response
    {
        return $this->render('about.html.twig');
    }

    #[Route("/report", name: "report")]
    public function report(): Response
    {
        return $this->render('report.html.twig');
    }

    #[Route("/api", name: "api")]
    public function apiHome(): Response
    {
        $routes = [
            'api_lucky_number' => '/api/lucky',
            'api_quote' => '/api/quote',
            'api_deck' => '/api/deck'
        ];

        return $this->render('api.html.twig', ['routes' => $routes]);
    }
}
