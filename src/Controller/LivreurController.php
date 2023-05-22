<?php

namespace App\Controller;

use App\Form\LEditProfilType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use PDO;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Address;
use App\Command\SendEmailCommand;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Mailer\Transport\SendmailTransport;
use App\Form\SearchColisType;
use App\Entity\Colis;
use App\Form\EtatType;
use App\Form\ChangePasswordType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Livreur;
use App\Form\LivreurType;
use App\Repository\UserRepository;
use App\Repository\LivreurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

class LivreurController extends AbstractController
{

    #[Route('/livreur/profil/modifier', name: 'livreur_profil_modifier')]
    public function editProfil(Request $request)
    {
        $livreur = $this->getUser();
        $form = $this->createForm(LEditProfilType::class, $this->getUser());
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $em= $this->getDoctrine()->getManager();
            $em->persist($livreur);
            $em->flush();

            $this->addFlash('message','Profil mise à jour');
            return $this->redirectToRoute('app_livreur');
        }
        return $this->render('livreur/editprofile.html.twig',[
            'form' => $form->createView(),
        ]);

    }

    #[Route('/livreur/pass/modifier', name: 'livreur_pass_modifier')]
    public function editPass(Request $request,UserPasswordEncoderInterface $passwordEncoder)
    {

        if($request->isMethod('POST'))
        {
            $em= $this->getDoctrine()->getManager();
            $livreur = $this->getUser();
            if($request->request->get('pass') == $request->request->get('pass2'))
            {
                $livreur->setPassword($passwordEncoder->encodePassword($livreur, $request->request->get('pass2')));
                $em->flush();
                $this->addFlash('message','Mot de passe mise à jours avec succès');

                return $this->redirectToRoute('app_livreur');
            }
            else
            {
                $this->addFlash('error','les deux mot de passe ne sont pas identiques');
            }
        }
        return $this->render('livreur/editpass.html.twig');

    }
    #[Route('/ShowLivreurReclamation/{id}', name: 'app_livreur_reclamation', methods: ['GET'])]
    public function showLivreurReclamation(int $id,ReclamationRepository $reclamationRepository): Response
    {   
        $reclamations = $reclamationRepository->findBy(['personne_reclame' => $id]);

        return $this->render('livreur/showLivreurReclamation.html.twig', [
            'reclamations' => $reclamations,
        ]);
    }

    /**
 * @Route("/livreur", name="livreur_index", methods={"GET"})
 */
public function showNullLivreurColis(Request $request, PaginatorInterface $paginator): Response
{
    $user = $this->getUser();
    $userID = $user->getId();
    $zone = $request->query->get('zone');

    try {
        $pdo = new PDO('mysql:host=localhost;dbname=taktakv;charset=utf8mb4', 'root', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql = "SELECT *
                FROM colis
                WHERE id_livreur IS NULL
                AND id_client IS NOT NULL
                AND id_partenaire = (SELECT id_partenaire FROM livreur WHERE user_id = :user_id)";
        $params = [
            ':user_id' => $userID,
        ];
        if ($zone) {
            $sql .= " AND zone LIKE :zone";
            $params[':zone'] = "%{$zone}%";
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $colis = $stmt->fetchAll();
        $pagination = $paginator->paginate(
            $colis,
            $request->query->getInt('page', 1),
            5
        );
    } catch (\PDOException $e) {
        throw new \PDOException($e->getMessage(), (int) $e->getCode());
    }

    return $this->render('livreur/livreurColis.html.twig', [
        'colisNullLivreur' => $pagination,
        'zone' => $zone,
        'userId' => $userID
    ]);
}



    /**
     * @Route("/livreur/{id}", name="livreur_show", methods={"GET"})
     */
    public function show($id, LivreurRepository $livreurRepository): Response
    {
        $user = $this->getUser();
        $userID = $user->getId();
        $livreur = $livreurRepository->findOneBy(['user' => $id]);
        if(!$livreur){
            throw $this->createNotFoundException('The livreur does not exist');
        }

        return $this->render('livreur/show.html.twig', [
            'livreur' => $livreur,
                'userId' => $userID
            ]);
    }

    /**
     * @Route("/livreur/{id}/edit", name="livreur_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, int $id, EntityManagerInterface $entityManager, MailerInterface $mailer,LivreurRepository $livreurRepository): Response
    {
        $user = $this->getUser();
        $userID = $user->getId();
        $livreur = $livreurRepository->findOneBy(['user' => $id]);
    
        if (!$livreur) {
            throw $this->createNotFoundException('Livreur not found');
        }
        $editForm = $this->createForm(LivreurType::class, $livreur);
        $editForm->handleRequest($request);
        if ($editForm->isSubmitted() && $editForm->isValid()) {
                $entityManager->flush();
                $transport = Transport::fromDsn('smtp://cheima.douiss@esprit.tn:12345654321280458@smtp.gmail.com:587');
                $mailer = new Mailer($transport);
                $email = (new Email());

                $email->from('email@gmail.com');

                // Set the "To address"
                $email->to('douisscheima4@gmail.com');
                $email->subject('Noreply email');
                $email->text("Vos informations ont été modifié !");
                $mailer->send($email);
                return $this->render('livreur/show_popup.html.twig', [
                    'livreur' => $livreur,
                        'userId' => $userID
                ]);
        }
        return $this->render('livreur/edit.html.twig', [
            'livreur' => $livreur,
            'edit_form' => $editForm->createView(),
                'userId' => $userID
            ]);
    }  
    
/**
 * @Route("/livreur/{id}", name="livreur_delete", methods={"DELETE"})
 */

    
 public function delete($id, EntityManagerInterface $entityManager): Response
{
    $user = $this->getUser();
    $userID = $user->getId();
    $livreurRepository = $entityManager->getRepository(Livreur::class);
    $livreur = $livreurRepository->find($id);

    if (!$livreur) {
        throw $this->createNotFoundException('The livreur does not exist');
    }
    // Set id_livreur to null for all colis assigned to this livreur
    $colisRepository = $entityManager->getRepository(Colis::class);
    $colis = $colisRepository->findBy(['livreur' => $livreur->getId()]);
    foreach ($colis as $c) {
        $c->setLivreur(null);
    }

    $entityManager->remove($livreur);
    $entityManager->flush();

    return $this->redirectToRoute('livreur_index',[
        'userId' => $userID
    ]);
}


/** 
 * @Route("/livreur/{id}/change-password", name="livreur_change_password")
 */
public function changePassword(Request $request, UserRepository $userRepository, EntityManagerInterface $entityManager, UserPasswordEncoderInterface $passwordEncoder, int $id): Response
{
    $user = $this->getUser();
    $userID = $user->getId();
    $livreur = $userRepository->find($id);
    if (!$livreur) {
        throw $this->createNotFoundException('Livreur not found.');
    }
    
    $formChange = $this->createForm(ChangePasswordType::class);
    $formChange->handleRequest($request);

    if ($formChange->isSubmitted() && $formChange->isValid()) {
        $currentPassword = $formChange->get('current_password')->getData();
        $newPassword = $formChange->get('new_password')->getData();

        if (!$passwordEncoder->isPasswordValid($livreur, $currentPassword)) {
            $formChange->get('current_password')->addError(new FormError('The current password is incorrect.'));
        } else {
            $encodedNewPassword = $passwordEncoder->encodePassword($livreur, $newPassword);
            $livreur->setPassword($encodedNewPassword);
            $entityManager->flush();

            $this->addFlash('success', 'Password changed successfully.');
            return $this->redirectToRoute('livreur_index',[
                'userId' => $userID
            ]);
        }
    }
    return $this->render('livreur/change_password.html.twig', [
        'formChange' => $formChange->createView(),
            'userId' => $userID
    ]);
}


 /**
     * @Route("/livreur/search", name="livreur_search_colis_by_zone", methods={"GET","POST"})
     */
    public function SearchColis(Request $request, ColisRepository $colisRepository)
    {
        $user = $this->getUser();
        $userID = $user->getId();
        $formSearch = $this->createForm(SearchColisType::class);

        $formSearch->handleRequest($request);

        if ($formSearch->isSubmitted() && $formSearch->isValid()) {
            $zone = $formSearch->get('search')->getData();
            $colisNullLivreur = $colisRepository->findBy(['zone' => $zone, 'id_livreur' => null]);
        } else {
            $colisNullLivreur = $colisRepository->findBy(['id_livreur' => null]);
        }

        return $this->render('livreur/index.html.twig', [
            'colisNullLivreur' => $colisNullLivreur,
            'formSearch' => $formSearch->createView(),
                'userId' => $userID
        ]);
    }
  /**
 * @Route("/colis/{id}/livreur/{user_id}", name="view_colis_details")
 */
public function viewColisDetails($id, $user_id)
{
    $user = $this->getUser();
    $userID = $user->getId();
    $pdo = new PDO('mysql:host=localhost;dbname=taktakv;charset=utf8mb4', 'root', '');
    $stmt = $pdo->prepare('SELECT id FROM livreur WHERE user_id=:user_id ');
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $id_livreur = $stmt->fetchColumn();
    
     $stmt = $pdo->prepare('UPDATE colis SET id_livreur=:id_livreur WHERE id = :id');
     $stmt->execute(['id_livreur' => $id_livreur, 'id' => $id]);
     $stmt->execute();
     $colis = $stmt->fetch();

     $stmt = $pdo->prepare('SELECT * FROM colis WHERE id_livreur = :id_livreur');
     $stmt->execute(['id_livreur' => $id_livreur]);
     $assignedColisList = $stmt->fetchAll();

    return $this->render('livreur/view_colis_details.html.twig', [
        'colis' => $colis,
        'assignedColisList' => $assignedColisList,
        'userId' => $userID
    ]);
}

/**
 * @Route("/livreur/colis/{id_livreur}", name="view_my_colis")
 */
public function viewMyColis( $id_livreur)
{
    $user = $this->getUser();
    $userID = $user->getId();
    $pdo = new PDO('mysql:host=localhost;dbname=taktakv;charset=utf8mb4', 'root', '');
    $stmt = $pdo->prepare('SELECT * FROM colis WHERE id_livreur = (SELECT id FROM livreur WHERE user_id=:user_id)');
    $stmt->bindParam(':user_id', $id_livreur);
    $stmt->execute();
    $assignedColisList = $stmt->fetchAll();
    return $this->render('livreur/view_colis_details.html.twig', [
        'assignedColisList' => $assignedColisList,
        'userId' => $userID
    ]);

}

/**
 * @Route("/livreur/update_etat_colis/{id}", name="update_etat_colis")
 */
public function updateEtatColis(Request $request, Colis $colis, EntityManagerInterface $entityManager, int $id)
{
    $user = $this->getUser();
    $userID = $user->getId();
    $form = $this->createFormBuilder()
        ->add('etat_colis', EtatType::class, [
            'label' => 'Etat colis',
            'data' => $colis->getEtatColis() ,
            'label_attr' => ['class' => 'etat_colis_container']
        ])
        ->getForm();

    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $data = $form->getData();
        $colis->setEtatColis($data['etat_colis']);

        $entityManager->persist($colis);
        $entityManager->flush();

        $this->addFlash('success', 'Etat colis mis à jour avec succès');
        return $this->redirectToRoute('livreur_index',[
            'userId' => $userID
        ]);
    }

    return $this->render('livreur/update_etat_colis.html.twig', [
        'form' => $form->createView(),
        'colis' => $colis,
        'userId' => $userID
    ]);
}
}
