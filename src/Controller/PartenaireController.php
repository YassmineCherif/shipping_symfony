<?php

namespace App\Controller;
use App\Entity\User;

use App\Form\LEditProfilType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Entity\Livreur;
use App\Entity\Partenaire;
use App\Form\PartenaireType;
use App\Form\LivreurType;
use App\Repository\LivreurRepository;
use App\Repository\PartenaireRepository;
use Doctrine\ORM\EntityManagerInterface;
use Monolog\DateTimeImmutable;
use PDO;
use App\Entity\Reclamation;
use App\Form\ReclamationType;
use App\Repository\ReclamationRepository;
use Symfony\Component\Validator\Constraints\DateTime;
 use Dompdf\Dompdf;
 use Dompdf\Options;
 use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
#[Route('/partenaire')]
class PartenaireController extends AbstractController
{


    #[Route('/partenaire', name: 'app_partenaire', methods: ['POST','GET'])]
    public function index(): Response
    {
        return $this->render('partenaire/show.html.twig', [
            'controller_name' => 'PartenaireController',
        ]);
    }
    

    #[Route('/partenaireedit', name: 'app_partenaire_show', methods: ['GET'])]
    public function show(EntityManagerInterface $entityManager,Request $request): Response
    {
        $partenaire = $this->getUser();
        $partenaire = $entityManager->getRepository(partenaire::class)->find($partenaire);
        return $this->render('partenaire/show.html.twig', [
            'partenaire' => $partenaire,
        ]);
    }



    #[Route('/partenaire/edit', name: 'app_partenaire_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, EntityManagerInterface $entityManager): Response
    {
        $partenaire = $this->getUser();
        $partenaire = $entityManager->getRepository(Partenaire::class)->find($partenaire->getId());

        $form = $this->createForm(PartenaireType::class, $partenaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $toEmail = $partenaire->getEmail();
            $nom = $partenaire->getNom();
            $numtel = $partenaire->getNumtel();
            $this->sendEmail($request, $toEmail, $entityManager, $nom,  $numtel);
        }

        return $this->render('partenaire/edit.html.twig', [
            'partenaire' => $partenaire,
            'form' => $form->createView(),
        ]);
    }


    #[Route('/mailling', name: 'app_mailling_edit')]
    public function sendEmail(Request $request,string $toEmail, EntityManagerInterface $entityManager,String $nom, String $numtel,): Response
    {
        $partenaire = $this->getUser();
        $partenaire = $entityManager->getRepository(partenaire::class)->find($partenaire);
       //mailing 
 $transport = Transport::fromDsn('smtp://najet.chebbi@esprit.tn:rdcmtkcqemzedcdt@smtp.gmail.com:587');
 $mailer = new Mailer($transport);
 $email = (new Email());
 $email->from('najet.chebbi@esprit.tn');
 $email->to( $toEmail);
 $email->subject(sprintf('Confirmé'));
 $email->text(sprintf('You have been successfully updated   '));
 $email->html(sprintf('
 <div style="background-color: #F5F5F5; padding: 20px; font-family: Arial, sans-serif;">
     <h1 style="color: #0073ff; font-size: 24px; font-weight: bold; text-align: center; margin-bottom: 30px;">Livreur assigné</h1>
     <div style="background-color: #fff; padding: 20px; border-radius: 10px;">
         <p style="color: #444; font-size: 18px; margin-bottom: 10px;">Vous avez été assigné à un %s avec la plaque dimmatriculation .</p>
         <p style="color: #444; font-size: 18px; margin-bottom: 10px;">Marque : </p>
         <p style="color: #444; font-size: 18px; margin-bottom: 10px;">Type : </p>
     </div>
     <div style="background-color: #0073ff; color: #fff; text-align: center; padding: 10px; margin-top: 30px; border-radius: 10px;">
         <p style="font-size: 14px; margin: 0;">Merci d utiliser notre service.</p>
     </div>
 </div>
 ',$nom,   $numtel));


 $mailer->send($email);
     return $this->redirectToRoute('app_partenaire_show', ['id' => $partenaire->getId()], Response::HTTP_SEE_OTHER);
     
 }

 #[Route('/partenaire/{id}', name: 'app_partenaire_delete', methods: ['POST'])]
 public function delete(Request $request,  EntityManagerInterface $entityManager): Response
 {
    $partenaire = $this->getUser();
        $partenaire = $entityManager->getRepository(partenaire::class)->find($partenaire);

     if ($this->isCsrfTokenValid('delete'.$partenaire->getId(), $request->request->get('_token'))) {
         $entityManager->remove($partenaire);
         $entityManager->flush();
     }

     return $this->redirectToRoute('app_partenaire_show', [], Response::HTTP_SEE_OTHER);
 }

/**
     * @Route("/search", name="app_livreur_search")
     */
    public function search(Request $request,EntityManagerInterface $entityManager,livreurRepository $repository): Response
    {
        $nom = $request->query->get('nom');
        $partenaire = $this->getUser();
        $livreurs = $repository->findByNoms($nom);

        return $this->render('partenaire/livreur.html.twig', [
            'livreurs' => $livreurs,
            'partenaire' => $partenaire,


        ]);
    }


    #[Route('/partenaire/livreur', name: 'app_partenaire_livreur', methods: ['GET','POST'])]
    public function Listelivreur(Request $request, LivreurRepository $livreurepository,PartenaireRepository $partenaireRepository,EntityManagerInterface $entityManager,): Response
    {
        $Nom = $request->query->get('Nom');

        $partenaire = $this->getUser();
        $partenaire = $entityManager->getRepository(partenaire::class)->find($partenaire);

        $livreurs = $livreurepository->findBy(['id_partenaire' => $partenaire]);
        if ($Nom) {
            $livreur = $livreurepository->findByNoms($Nom);
        } else {
            $livreur= $livreurepository->findAll();
        }
    
      
       
        return $this->render('partenaire/livreur.html.twig', [
            'partenaire' => $partenaire,
            'livreurs' => $livreurs,
            'livreur' => $livreur,
        ]);
    }

    #[Route('/partenaire/livreur/new/{id}', name: 'app_Livreur_new', methods: ['GET', 'POST'])]
    public function Newlivreur(Request $request, EntityManagerInterface $entityManager, int $id): Response
    {
        $partenaire = $this->getUser();
        $partenaire = $entityManager->getRepository(Partenaire::class)->find($id);
        if (!$partenaire) {
            throw $this->createNotFoundException('Le partenaire avec l\'ID '.$id.' n\'a pas été trouvé.');
        }

        $livreur = new Livreur();
        $livreur->setIdPartenaire($partenaire);

        $form = $this->createForm(LivreurType::class, $livreur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($livreur);
            $entityManager->flush();

            //mailing
            $transport = Transport::fromDsn('smtp://najet.chebbi@esprit.tn:rdcmtkcqemzedcdt@smtp.gmail.com:587?verify_peer=0');
            $mailer = new Mailer($transport);
            $email = (new Email());
            $email->from('najet.chebbi@esprit.tn');
            $email->to('najet.chebbi@esprit.tn');
            $email->subject(sprintf('confirmé'));
            $email->text(sprintf('You have been added to partenaire with   '));
            $email->html(sprintf('<h1>Un nouveau livreur a été ajouté avec succès.</h1>'));

            $mailer->send($email);
            return $this->redirectToRoute('app_partenaire_livreur', ['id' => $partenaire->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('partenaire/newlivreur.html.twig', [
            'partenaire' => $partenaire,
            'livreur' => $livreur,
            'form' => $form,
        ]);
    }
    #[Route('/{id}', name: 'app_livreur_delete', methods: ['POST'])]
    public function deleteLivreur(Request $request, Livreur $livreur, EntityManagerInterface $entityManager): Response
    { $partenaire = $this->getUser();
         $partenaireId = $livreur->getIdPartenaire()->getId();
        
        if ($this->isCsrfTokenValid('delete'.$livreur->getId(), $request->request->get('_token'))) {
            $entityManager->remove($livreur);
            $entityManager->flush();
        }
       

        return $this->redirectToRoute('app_partenaire_livreur', ['id' => $partenaireId], Response::HTTP_SEE_OTHER);
    }
    #[Route('/ShowPartenaireReclamation/{id}', name: 'app_partenaire_reclamation', methods: ['GET'])]
    public function showPartenaireReclamation(int $id): Response
    {   
        try {
            $sql = 'SELECT R.text,R.personne_reclame, R.type_reclamation,R.date , R.ref FROM livreur as L,reclamation as R WHERE L.login = R.personne_reclame and L.id_partenaire = :id_partenaire';
            $pdo = new PDO('mysql:host=localhost;dbname=taktakv;charset=utf8mb4', 'root', '');
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id_partenaire', $id, PDO::PARAM_INT);
            $stmt->execute();
            $reclamations = $stmt->fetchAll();
        } catch (\PDOException $e) {
            throw new \PDOException($e->getMessage(), (int) $e->getCode());
        }

        return $this->render('partenaire/showPartenaireReclamation.html.twig', [
            'reclamations' => $reclamations,
        ]);
    }
}


    

