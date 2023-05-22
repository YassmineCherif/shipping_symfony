<?php

namespace App\Controller;
use App\Form\LivreurAvailabilityType;
use App\Entity\LivreurAvailability;
use App\Entity\Livreur;
use App\Repository\LivreurRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\LivreurAvailabilityRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class LivreurAvailabilityController extends AbstractController
{
    
    /**
* @Route("/livreur/{id}/addAvailability", name="livreur_availability_add", methods={"GET", "POST"})
* @ParamConverter("livreur", options={"mapping": {"id": "id"}})
*/
public function addLivreurAvailability(Request $request, EntityManagerInterface $entityManager, int $id, LivreurRepository $livreurRepository): Response
{
    $user = $this->getUser();
    $userID = $user->getId();
    $livreurId= $livreurRepository->findOneBy(['user' => $userID]);
    $livreurAvailability = $entityManager->getRepository(LivreurAvailability::class)->findOneBy(['livreur' => $livreurId], ['id' => 'DESC']);

    if (!$livreurAvailability) {
        $livreurAvailability = new LivreurAvailability();
        $livreurAvailability->setIdLivreur($livreur->getId());
    }

    $form = $this->createForm(LivreurAvailabilityType::class, $livreurAvailability);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        if ($livreurAvailability->isStatus() === 'unavailable') {
            $livreurAvailability->setReason($request->request->get('reason'));
        }

        $entityManager->persist($livreurAvailability);
        $entityManager->flush();

        $this->addFlash('success', 'Availability updated successfully!');

        return $this->redirectToRoute('livreur_index',[
            'userId' => $userID
        ]);
    }

    return $this->render('livreur_availability/addAvailability.html.twig', [
        'add_form' => $form->createView(),
        'userId' => $userID
    ]);
}

}