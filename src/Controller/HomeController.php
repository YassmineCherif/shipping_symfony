<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Colis;

class HomeController extends AbstractController
{

    #[Route('/', name: 'app_accueil')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig',[
            'tracking_status' => ""

        ]);
    }
    
    #[Route('/', name: 'app_track', methods: ['GET'])]
    public function trackColis(Request $request, EntityManagerInterface $entityManager): Response
    {
        $trackingNumber = $request->query->get('ref');
        $trackingStatus = '';

        if ($trackingNumber) {
            $tracking = $entityManager->getRepository(Colis::class)->findOneBy(['ref' => $trackingNumber]);

            if ($tracking) {
                $trackingStatus = $tracking->getEtatColis();
            } else {
                $trackingStatus = 'Tracking number not found';
            }
        }

        return $this->render('home/index.html.twig', [
            'controller_name' => 'TrackColisController',
            'tracking_number' => $trackingNumber,
            'tracking_status' => $trackingStatus
        ]);
    }
}
