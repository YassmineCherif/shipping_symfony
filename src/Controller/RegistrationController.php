<?php

namespace App\Controller;
use App\Entity\Livreur;
use App\Entity\Client;
use App\Entity\Partenaire;
use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\EmailVerifier;
use App\Security\UserAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints as Assert;


class RegistrationController extends AbstractController
{
    private EmailVerifier $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, UserAuthenticatorInterface $userAuthenticator, UserAuthenticator $authenticator, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
    
            //hashing the user's password before it is stored in the database
            $user->setPassword(
                //$userPasswordHasher is a service that provides a secure way to hash the password
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
    
            // Get the value of the "type" checkbox
            $type = $form->get('type')->getData();
            $user->setType($type);
    
            $entityManager->persist($user);
            $entityManager->flush();
    
            // move user data to specific table
            $this->move_User_to_Table($entityManager, $user);
    
            //generate a signed url and email it to the user
            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
              (new TemplatedEmail())
                ->from(new Address('indila205@gmail.com', 'security'))
                ->to($user->getEmail())
                ->subject('Please Confirm your Email')
                ->htmlTemplate('registration/confirmation_email.html.twig')
             );
    
            return $userAuthenticator->authenticateUser(
                $user,
                $authenticator,
                $request
            );
        }
    
        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $this->getUser());
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('app_register');
        }

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $this->addFlash('success', 'Your email address has been verified.');

        return $this->redirectToRoute('app_register');
    }


    #[Route('/move', name: 'move_user')]
public function move_User_to_Table(EntityManagerInterface $entityManager)
{
    $user = $entityManager->getRepository(User::class)->findOneBy([], ['id' => 'DESC']);

    if ($user) {
        switch ($user->getType()) {
            case 'client':
                $client = new Client();
                $client->setUser($user); // link the User and Client entities
                $client->setNom($user->getNom());
                $client->setPrenom($user->getPrenom());
                $client->setEmail($user->getEmail());
                $client->setAdresse($user->getAdresse());
                $client->setNumtel($user->getNumtel());
                $client->setPassword($user->getPassword());

                $entityManager->persist($user); // persist the User entity
                $entityManager->persist($client);
                break;
            case 'livreur':
                $livreur = new Livreur();
                $livreur->setUser($user); // link the User and Livreur entities
                $livreur->setNom($user->getNom());
                $livreur->setPrenom($user->getPrenom());
                $livreur->setEmail($user->getEmail());
                $livreur->setAdresse($user->getAdresse());
                $livreur->setNumtel($user->getNumtel());
                $livreur->setPassword($user->getPassword());

                $entityManager->persist($user); //  persist the User entity
                $entityManager->persist($livreur);
                break;
            case 'partenaire':
                $partenaire = new Partenaire();
                $partenaire->setUser($user); // link the User and Partenaire entities
                $partenaire->setNom($user->getNom() . ' ' . $user->getPrenom());
                $partenaire->setEmail($user->getEmail());
                $partenaire->setNumtel($user->getNumtel());
                $partenaire->setPassword($user->getPassword());

                $entityManager->persist($user); //  persist the User entity
                $entityManager->persist($partenaire);
                break;
            default:
                // handle this error however you like
                throw new \Exception('Invalid user type');
        }
        $entityManager->flush();
    }
}




}



