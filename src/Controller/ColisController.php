<?php

namespace App\Controller;
use App\Entity\User;
use App\Entity\Colis;
use App\Entity\Client;
use App\Repository\ClientRepository;
use App\Entity\Livreur;

use App\Entity\Partenaire;
use App\Repository\ColisRepository;
use App\Form\AjoutColisType;
use Knp\Component\Pager\PaginatorInterface;
use MercurySeries\FlashyBundle\DependencyInjection\MercurySeriesFlashyExtension;
use Dompdf\Dompdf;
use Dompdf\Options;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Form\RegisterType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ColisController extends AbstractController
{
    private $entityManager;
    public function __construct(EntityManagerInterface $entityManager){
        $this->entityManager = $entityManager;
    }


    #[Route('/historique', name: 'app_historique')]
    public function historique(Request $request, ColisRepository $repo, PaginatorInterface $paginator, EntityManagerInterface $entityManager): Response
    {

        $user = $this->getUser();
        $userID = $user->getId();
        $email = $user->getEmail();
    
        $client = $entityManager->getRepository(Client::class)->findOneBy(['email' => $email]);
        $id = $client->getId();

        $colis = $repo->findBy(
            ['id_client' => $id, 'etat_colis' => 'Livré'],
            ['id' => 'DESC']
        );        
        

        
        $colis = $paginator->paginate(
            $colis, /* query NOT result */
            $request->query->getInt('page', 1),
            5
        );

        return $this->render('colis/historique.html.twig', [
            'colis' => $colis,
            'userId' => $userID
        ]);
    }

    #[Route('/colis', name: 'app_colis')]
    public function index(ColisRepository $repo, Request $request, PaginatorInterface $paginator , EntityManagerInterface $entityManager,): Response
    {
        $user = $this->getUser();
        $userID = $user->getId();
        $email = $user->getEmail();
    
        $client = $entityManager->getRepository(Client::class)->findOneBy(['email' => $email]);
        $id = $client->getId();
      

       // $email = $request->query->get('email');
        //$client = $repository->findByNom($email);
        
        $searchQuery = $request->request->get('query');
        $colisEnCours = $repo->findBy(
            ['etat_colis' => 'En cours', 'id_client' => $id],
            ['id' => 'DESC']
        );
        
    
        if ($searchQuery) {
            $colisEnCours = array_filter($colisEnCours, function ($colis) use ($searchQuery) {
                return stripos($colis->getRef(), $searchQuery) !== false;
            });
        }

        $colisEnCours = $paginator->paginate(
            $colisEnCours, /* query NOT result */
            $request->query->getInt('page', 1),
            5
        );
    
        return $this->render('colis/index.html.twig', [
            'colis' => $colisEnCours,
            'client' => $client,
            'id' => $id,
            'searchQuery' => $searchQuery,
            'userId' => $userID

        ]);
    }


    #[Route('/colis/add', name: 'app_add')]
    public function add(Request $request,EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $userID = $user->getId();

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
       $user = $this->getUser();
        $email = $user->getEmail();
    
        $client = $entityManager->getRepository(Client::class)->findOneBy(['email' => $email]);
        //$id = $client->getId();

        $colis->setIdClient($client);
        $colis->setPrix($prix_final);

            
            $this->entityManager->persist($colis);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_confirm', ['id' => $colis->getId(),'userId' => $userID
        ]);
        }

        return $this->render('colis/add.html.twig', [
            'form' => $form->createView(),
            'userId' => $userID

        ]);
    }

    #[Route('/colis/confirm/{id}', name: 'app_confirm')]
    public function confirm(Request $request, ColisRepository $repo, int $id): Response
    {
        $user = $this->getUser();
        $userID = $user->getId();

        $colis = $repo->find($id);
    
        if (!$colis) {
            throw $this->createNotFoundException('Le colis n\'existe pas');
        }
    
        if ($request->getMethod() === 'POST') {
            if ($request->request->has('valider')) {
                $this->entityManager->persist($colis);
                $this->entityManager->flush();
                return $this->redirectToRoute('app_methode',[
                    'userId' => $userID
                ]);
            } else if ($request->request->has('annuler')) {
                $this->entityManager->remove($colis);
                $this->entityManager->flush();
                return $this->redirectToRoute('app_methode',[
                    'userId' => $userID
                ]);
            }
        }
    
        return $this->render('colis/confirm.html.twig', [
            'colis' => $colis,
            'userId' => $userID

        ]);
    }
    
    #[Route('/colis/methode', name: 'app_methode')]
    public function methode(ColisRepository $repo): Response
    {
        $user = $this->getUser();
        $userID = $user->getId();


        return $this->render('colis/methode.html.twig',[
            'userId' => $userID
        ]);
    }

    #[Route('/colis/carte', name: 'app_carte')]
    public function carte(ColisRepository $repo): Response
    {
        $user = $this->getUser();
        $userID = $user->getId();


        return $this->render('colis/carte.html.twig',[
            'userId' => $userID
        ]);
    }
    

    #[Route('/colis/delete/{id}', name: 'app_delete', methods: ['DELETE','POST','GET'])]
    public function delete(Colis $colis, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $userID = $user->getId();

        $entityManager->remove($colis);
        $entityManager->flush();
    
    
        return $this->redirectToRoute('app_colis',[
            'userId' => $userID
        ]);
    }


    #[Route('/colis/{id}/edit', name: 'app_edit')]
    public function edit(Colis $colis, Request $request): Response
    {
        $user = $this->getUser();
        $userID = $user->getId();


        $form = $this->createForm(AjoutColisType::class, $colis);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $this->entityManager->persist($colis);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_colis',[
                'userId' => $userID
            ]);
        }
        return $this->render('colis/edit.html.twig', [
            'form'=> $form ->createView(),
            'userId' => $userID

        ]
        );
    }


    #[Route('/colis/pdf/{id}', name: 'app_pdf')]
    public function pdf(ColisRepository $repo,  $id,): Response
    {
        $user = $this->getUser();
        $userID = $user->getId();


                // Configure Dompdf according to your needs
                $pdfOptions = new Options();
                $pdfOptions->set('defaultFont', 'Arial');
                
                // Instantiate Dompdf with our options
                $dompdf = new Dompdf($pdfOptions);
                $coli = $repo->find($id);

        if(!$coli){
            return $this->redirectToRoute('app_colis',[
                'userId' => $userID
            ]);
        }
                
                // Retrieve the HTML generated in our twig file
                $html = $this->renderView('colis/show.html.twig', [
                    'coli' => $coli,
                    'userId' => $userID
                ]);
                
                // Load HTML to Dompdf
                $dompdf->loadHtml($html);
                
                // (Optional) Setup the paper size and orientation 'portrait' or 'portrait'
                $dompdf->setPaper('A4', 'portrait');
        
                // Render the HTML as PDF
                $dompdf->render();
        
                // Output the generated PDF to Browser (force download)
                $dompdf->stream("mypdf.pdf", [
                    "Attachment" => false
                ]);
        
    }

    #[Route('/colis/{id}', name: 'app_show')]
    public function show(ColisRepository $repo,  $id): Response
    {
        $user = $this->getUser();
        $userID = $user->getId();

        $coli = $repo->find($id);

        if(!$coli){
            return $this->redirectToRoute('app_colis',[
                'userId' => $userID
            ]);
        }

        return $this->render('colis/show.html.twig', [
            'coli' => $coli,
            'userId' => $userID

        ]);
    }
}
