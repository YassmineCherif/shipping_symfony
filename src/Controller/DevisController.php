<?php
namespace App\Controller;

use App\Entity\Colis;
use App\Entity\Partenaire;
use App\Repository\ColisRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\AjoutColisType;

class DevisController extends AbstractController
{
    private $entityManager;
    public function __construct(EntityManagerInterface $entityManager){
        $this->entityManager = $entityManager;
    }

    #[Route('/devis', name: 'app_devis')]
    public function index(Request $request): Response
    {
        $colis = new Colis();
        $form = $this->createForm(AjoutColisType::class, $colis);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $colis=$form->getData();

            function random_string($length = 10) {
                $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                $result = '';
                for ($i = 0; $i < $length; $i++) {
                    $result .= $characters[rand(0, strlen($characters) - 1)];
                }
                return $result;
            }
            $random_ref = random_string(); // Stocke la chaîne aléatoire dans une variable
            $colis->setRef($random_ref);
            $colis->setEtatColis('En cours');


            $nom_partenaire = $colis->getIdPartenaire();
$partenaire = $this->entityManager->getRepository(Partenaire::class)->findOneBy(['id' => $nom_partenaire]);
$prix = $partenaire->getPrixPoids();
$poids = $colis->getPoids();
if ($colis->getPoids() < 5) {
    $prix_final = $prix;
} else {
    $prix_final = $prix * 1.05;
}

$colis->setPrix($prix_final);

            
            $this->entityManager->persist($colis);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_cout', ['id' => $colis->getId()]);
        }

        return $this->render('devis/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/devis/cout/{id}', name: 'app_cout')]
    public function confirm(Request $request, ColisRepository $repo, int $id): Response
    {
        $colis = $repo->find($id);
    
        if (!$colis) {
            throw $this->createNotFoundException('Le colis n\'existe pas');
        }
    
        if ($request->getMethod() === 'POST') {
            if ($request->request->has('valider')) {
                $this->entityManager->persist($colis);
                $this->entityManager->flush();
                return $this->redirectToRoute('app_methode');
            } else if ($request->request->has('annuler')) {
                $this->entityManager->remove($colis);
                $this->entityManager->flush();
                return $this->redirectToRoute('app_home');
            }
        }
    
        return $this->render('devis/cout.html.twig', [
            'colis' => $colis,
        ]);
    }
}
