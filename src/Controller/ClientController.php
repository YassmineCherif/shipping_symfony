<?php

namespace App\Controller;

use App\Form\ResetPasswordRequestFormType;
use App\Entity\Client;
use App\Entity\Livreur;
use App\Entity\User;
use App\Form\EditProfilType;
use App\Form\RegistrationFormType;
use App\Repository\LivreurRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;


class ClientController extends AbstractController
{




    #[Route('/client', name: 'app_client')]
    public function index(Request $request): Response
    {
        $user = $this->getUser();
        $userType = $user->getType(); // Assuming the user type is stored in the "type" property
        $userID = $user->getId();

        $form = $this->createFormBuilder()
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Client' => 'client',
                    'Livreur' => 'livreur',
                    'Partenaire' => 'partenaire',
                ],
                'label' => 'Type',
                'placeholder' => 'Choose a type',
            ])
            ->add('submit', SubmitType::class, ['label' => 'confirm'])
            ->getForm();
    
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $selectedType = $data['type'];
    
            if ($selectedType === $userType) {
                if ($userType === 'livreur') {
                    return $this->render('livreur/successliv.html.twig',[
                        'userId' => $userID
                    ]);
                } elseif ($userType === 'client') {
                    return $this->render('client/successclient.html.twig',[
                        'userId' => $userID
                    ]);
                } else {
                    return $this->render('partenaire/successpart.html.twig',[
                        'userId' => $userID
                    ]);
                }
            } else {
                $this->addFlash('error', 'Vous n\'avez pas accès.');
            }

        }

    
        return $this->render('client/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/client/profil/modifier', name: 'client_profil_modifier')]
    public function editProfil(Request $request)
    {
        $userr = $this->getUser();
        $userID = $userr->getId();
        $form = $this->createForm(EditProfilType::class, $this->getUser());
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
                // Update the user profile
            $em= $this->getDoctrine()->getManager();
            $em->persist($userr);
            $em->flush();

               // Update the client profile
             $clientRepo = $em->getRepository(Client::class);
             $client = $clientRepo->findOneBy(['email' => $userr->getEmail()]);

             if ($client) {
                $client->setNom($form->get('nom')->getData());
                $client->setPrenom($form->get('prenom')->getData());
                $client->setAdresse($form->get('adresse')->getData());
                $client->setNumtel($form->get('numtel')->getData());

                $em->persist($client);
                $em->flush();
            } 


            $this->addFlash('message','Profil mise à jour');
            return $this->redirectToRoute('homee',[
                'userId' => $userID
            ]);
        }
        return $this->render('client/editprofile.html.twig',[
            'form' => $form->createView(),
            'userId' => $userID
        ]);

    }

    #[Route('/client/pass/modifier', name: 'client_pass_modifier')]
    public function editPass(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        if ($request->isMethod('POST')) {
            $em = $this->getDoctrine()->getManager();
            $user = $this->getUser();
            if ($request->request->get('pass') == $request->request->get('pass2')) {
                $encodedPassword = $passwordEncoder->encodePassword($user, $request->request->get('pass2'));
                $user->setPassword($encodedPassword);
                $em->flush();
    
                // Update password in client table
                $clientRepository = $em->getRepository(Client::class);
                $client = $clientRepository->findOneBy(['email' => $user->getEmail()]);
                if ($client) {
                    $client->setPassword($encodedPassword);
                    $em->flush();
                }
    
                $this->addFlash('message', 'Mot de passe mise à jours avec succès');
    
                return $this->redirectToRoute('homee');
            } else {
                $this->addFlash('error', 'les deux mot de passe ne sont pas identiques');
            }
        }
    
        return $this->render('client/editpass.html.twig');
    }
    

    #[Route('/client/listlivreur', name: 'listlivreur')]
    public function listLivreur(Request $request,LivreurRepository $livreurRepository): Response
{

    $prenom = $request->query->get('prenom');

     if ($prenom) {
    $livreurNom = $livreurRepository->findByNom($prenom);
   } 
    else {
        //$livreur = $livreurRepository->findLivreurWithPartenaire($id_partenaire);
        $livreurNom = $livreurRepository->findAll();
    }
    //récupère la valeur de la clé sortByNom
    $sortByNom = $request->query->get('sortByNom');

    if ($sortByNom) { //si elle existe 
        // prend en premier argument le tableau à trier et la fonction de comparaison
        usort($livreurNom, function($a, $b) {
            //strcmp() compare deux chaînes de caractères
            return strcmp($a->getPrenom(), $b->getPrenom());
        });
    }

    return $this->render('client/list.html.twig', [ 'sortByNom' => $sortByNom, 'livreur' => $livreurNom ]);
}

/*
    #[Route('/client/search', name: 'Livreur_search')]
    public function search(Request $request, LivreurRepository $repository): Response
    {
        $prenom = $request->query->get('prenom');
        $livreurNom = $repository->findByNom($prenom);
        return $this->render('client/list.html.twig', ['livreurNom' => $livreurNom,]);
    }
*/

    #[Route('/client/logout', name: 'logout')]
    public function logout(AuthenticationUtils $authenticationUtils): Response
    {
        $response = new RedirectResponse($this->generateUrl('app_login'));

        // Clearing the session
        $this->get('session')->clear();

        // Invalidate the cookie
        $response->headers->clearCookie('PHPSESSID');

        return $response;
    }

    #[Route('/client/homee', name: 'homee')]
    public function homee(AuthenticationUtils $authenticationUtils): Response
    {
        return $this->render('client/successclient.html.twig');
    }


    /*#[Route('/client/delete', name: 'delete')]
    public function deleteAccount(Security $security): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $userEmail = $user->getEmail();
    
        // Supprimer le compte de l'utilisateur
        $entityManager->remove($user);
        $entityManager->flush();
    
        // Récupérer le user object de nouveau avec l'identifiant
        $user = $entityManager->getRepository(User::class)->find($userEmail);
    
        // Rediriger vers la page d'accueil
        return $this->redirectToRoute('homee');
    }*/

    
    
   /**
 * @Route("/resetPassword", name="reset_password_request", methods={"GET", "POST"})
 */
public function request(Request $request, UserPasswordEncoderInterface $passwordEncoder): Response
{
    // crée un formulaire de demande de réinitialisation de mdp
    $form = $this->createForm(ResetPasswordRequestFormType::class);
    // récupère le jeton CSRF pour le formulaire de mdp
    $csrfToken = $this->get('security.csrf.token_manager')->getToken('reset_password_request_form')->getValue();
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
         //recupere l'email de l'utilisateur à partir du formulaire
        $email = $form->get('email')->getData();
        $entityManager = $this->getDoctrine()->getManager();
        $user = $entityManager->getRepository(User::class)->findOneByEmail($email);

        if (!$user) {
            //si on a pas trouvé l'email
            $this->addFlash('reset_password_error', 'Email could not be found.');
            return $this->redirectToRoute('reset_password_request');
        }
        
       // $yass = "1234";

       //generer un nouveau mdp avec les fonctions random_bytes et bin2hex
       //random_bytes generates a string of random bytes length = 8 
       //bin2hex converts bytes into a hexadecimal string
        $newPassword = bin2hex(random_bytes(8)); 

        // Encode the password using the password encoder
        $encodedPassword = $passwordEncoder->encodePassword($user, $newPassword); 
        // Set the new password for the user
        $user->setPassword($encodedPassword); 

        if($user->getType() == 'livreur') {
            $livreur = $entityManager->getRepository(Livreur::class)->findOneByUser($user);
            $livreur->setPassword($encodedPassword);
        } else if ($user->getType() == 'client') {
            $client = $entityManager->getRepository(Client::class)->findOneByUser($user);
            $client->setPassword($encodedPassword);
        } else if ($user->getType() == 'partenaire') {
            $partenaire = $entityManager->getRepository(Partenaire::class)->findOneByUser($user);
            $partenaire->setPassword($encodedPassword);
        }
        
        $entityManager->flush();

           
            //Transport : class de mailer pour envoyer le mail      DSN: configurer le transport en
            $transport = Transport::fromDsn('smtp://indila205@gmail.com:gmyneaovxeqnvvxn@smtp.gmail.com:587?verify_peer=0');
            //creation de l'instance de la classe Mailer de en utilisant le transport
            $mailer = new Mailer($transport); 

            //creer un objet Email 
            $email = (new Email());
            $email->from('indila205@gmail.com');
            $email->to($user->getEmail());
            $email->subject(sprintf('Reset your password '));
            $email->text(sprintf('Reset'));
            $email->html(sprintf('<h1>Hi , welcome to our website TakTak company 🙂  your new password is </h1> %s', $newPassword));

               try {
                     $mailer->send($email);
                     die('<style> * { color: #fff; background-color: green; } </style><pre><h1> Password reset email sent ! </h1></pre>');
                   } catch (TransportExceptionInterface $e) {
                    var_dump($e);
                    die('<style>* { color: #fff; background-color: red; }</style><pre><h1>Error sending password reset email !</h1></pre>');
                }
        }

        return $this->render('reset_password/request.html.twig', [
            'requestForm' => $form->createView(),
            'csrf_token' => $csrfToken, // Add CSRF token to the view
        ]);
    }


    



    
    #[Route('/client/loginagain', name: 'loginagain')]
    public function loginagain (AuthenticationUtils $authenticationUtils): Response
{
    return $this->render('security/login.html.twig', [
        'error' => $authenticationUtils->getLastAuthenticationError(),
        'last_username' => $authenticationUtils->getLastUsername(),
    ]);
}

#[Route('/ShowUserReclamation/{id}', name: 'app_user_reclamation', methods: ['GET'])]
public function showUserReclamation(int $id,ReclamationRepository $reclamationRepository): Response
{   
    $user = $this->getUser();
    $userID = $user->getId();
    $reclamations = $reclamationRepository->findBy(['id_client' => $id]);

    return $this->render('client/clientReclamation.html.twig', [
        'reclamation' => $reclamations,
    ]);
}

#[Route('/userReclamation/delete/{id}', name: 'app_delete_reclamation', methods: ['DELETE','POST','GET'])]
public function delete(Reclamation $reclamation, EntityManagerInterface $entityManager): Response
{
    $user = $this->getUser();
    $userID = $user->getId();
    $entityManager->remove($reclamation);
    $entityManager->flush();


    return $this->redirectToRoute('app_user_reclamation', ['id' => 8]);
}

#[Route('/ajouterReclamation/{id}', name: 'app_post_feedback', methods: ['GET','POST'])]
public function postFeedback(int $id, Request $request ,EntityManagerInterface $entityManager): Response
{
    $user = $this->getUser();
    $userID = $user->getId();

        $reclamation = new Reclamation();

        $form = $this->createForm(ReclamationType::class, $reclamation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $reclamation->setIdClient(8);
            $now = new DateTimeImmutable(false);    
            $reclamation->setDate($now);
            $entityManager->persist($reclamation);
            $entityManager->flush();
            // redirect to success page
            return $this->redirectToRoute('app_user_reclamation', ['id' =>$userID, 'userId' => $userID ],
            
        );
        }


    return $this->render('client/postReclamation.html.twig', [
        'form' => $form->createView(),
        'userId' => $userID
    ]);
}

#[Route('/modifierFeedback/{id}', name: 'app_modify_feedback', methods: ['GET','UPDATE','POST'])]
public function edit(Request $request, int $id, EntityManagerInterface $entityManager): Response
{
    $user = $this->getUser();
    $userID = $user->getId();
    $reclamation = $entityManager->getRepository(Reclamation::class)->find($id);

    if (!$reclamation) {
        throw $this->createNotFoundException('reclamation not found');
    }

    $editForm = $this->createForm(ReclamationType::class, $reclamation);
    $editForm->handleRequest($request);
    if ($editForm->isSubmitted() && $editForm->isValid()) {
        $entityManager->flush();
        return $this->redirectToRoute('app_user_reclamation', ['id' =>$userID]);
    }

    return $this->render('client/changeReclamation.html.twig', [
        'reclamation' => $reclamation,
        'edit_form' => $editForm->createView(),
        'userId' => $userID
    ]);
}


}
