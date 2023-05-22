<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Colis;

class TrackColisController extends AbstractController
{
    #[Route('/track/colis', name: 'app_track_colis', methods: ['GET'])]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $trackingNumber = $request->query->get('ref');
        $trackingStatus = '';

        if ($trackingNumber) {
            $tracking = $entityManager->getRepository(Colis::class)->findOneBy(['ref' => $trackingNumber]);

            if ($tracking) {
                $trackingStatus = $tracking->getEtatColis();
            }
        }

        return $this->render('track_colis/index.html.twig', [
            'controller_name' => 'TrackColisController',
            'tracking_number' => $trackingNumber,
            'tracking_status' => $trackingStatus,
        ]);
    }

}
