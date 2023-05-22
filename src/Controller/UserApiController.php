<?php
namespace App\Controller;

use App\Entity\User;
use App\Entity\Livreur;
use App\Entity\Partenaire;
use App\Entity\Client;
use App\Repository\ColisRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use App\Entity\Colis;
use Symfony\Component\Security\Core\Security;

class UserApiController extends AbstractController
{


    #[Route('registermobile', name: 'app_register_mobile')]
    public function registerAction(Request $request, UserPasswordEncoderInterface $userPasswordHasher)
    {
        $nom = $request->query->get("nom");
        $prenom = $request->query->get("prenom"); 
        $email = $request->query->get("email");
        $adresse = $request->query->get("adresse");
        $numtel = $request->query->get("numtel");
        $type = $request->query->get("type");
        $password = $request->query->get("password");


        if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
           return new Response("email invalid");
        }

        $user = new User();
        $user->setNom($nom);
        $user->setPrenom($prenom); 
        $user->setEmail($email);
        $user->setAdresse($adresse);
        $user->setNumtel($numtel);
        $user->setType($type);
     
       // Hashing the user's password before storing it in the database
      $user->setPassword(
        $userPasswordHasher->encodePassword(
            $user,
            $password
        )
       );  
        try {
         $em = $this->getDoctrine()->getManager();    
         $em->persist($user);
         $em->flush();
         
         $user = $em->getRepository(User::class)->findOneBy([], ['id' => 'DESC']);

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
     
                     $em->persist($user); // persist the User entity
                     $em->persist($client);
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
     
                     $em->persist($user); //  persist the User entity
                     $em->persist($livreur);
                     break;
                 case 'partenaire':
                     $partenaire = new Partenaire();
                     $partenaire->setUser($user); // link the User and Partenaire entities
                     $partenaire->setNom($user->getNom() . ' ' . $user->getPrenom());
                     $partenaire->setEmail($user->getEmail());
                     $partenaire->setNumtel($user->getNumtel());
                     $partenaire->setPassword($user->getPassword());
     
                     $em->persist($user); //  persist the User entity
                     $em->persist($partenaire);
                     break;
                 default:
                     // handle this error however you like
                     throw new \Exception('Invalid user type');
             }
             $em->flush();
         }

         return new JsonResponse("Account is created", 200);
        } catch (\Exception $ex) {
           return new Response("exception".$ex->getMessage());
       }
    }




    #[Route('loginmobile', name: 'app_login_mobile')]
public function loginAction(Request $request)
{
    $email = $request->query->get("email");
    $password = $request->query->get("password");

    $em = $this->getDoctrine()->getManager(); 
    $user= $em->getRepository(User::class)->findOneBy(['email'=>$email]);

    if ($user) {
        if (password_verify($password, $user->getPassword())) {
            $serializer = new Serializer([new ObjectNormalizer()]);
            $formatted = $serializer->normalize($user);
            $type = $user->getType();
            return new JsonResponse(['user' => $formatted, 'type' => $type]);
        } else {
            return new JsonResponse(['error' => 'Mot de passe incorrect'], Response::HTTP_UNAUTHORIZED);
        }
    } else {
        return new JsonResponse(['error' => 'Utilisateur introuvable'], Response::HTTP_NOT_FOUND);
    }
}

#[Route('editmobile', name: 'app_edit_mobile')]
public function editProfileAction(Request $request, UserPasswordEncoderInterface $userPasswordHasher)
{
    $id = $request->query->get("id");
    $email = $request->query->get("email");
    $nom = $request->query->get("nom");
    $prenom = $request->query->get("prenom");
    $numtel = $request->query->get("numtel");

    $em = $this->getDoctrine()->getManager(); 
    $user = $em->getRepository(User::class)->find($id);

    if (!empty($email)) {
        $user->SetEmail($email);
    }

    if (!empty($nom)) {
        $user->setNom($nom);
    }

    if (!empty($prenom)) {
        $user->setPrenom($prenom); 
    }

    if (!empty($numtel)) {
        $user->setNumtel($numtel);
    }

    // Update user table
    try {
        $em->persist($user);
        $em->flush();
    } catch (\Exception $ex) {
        return new Response("fail: ".$ex->getMessage());
    }

    $userData = array(
        'id' => $user->getId(),
        'email' => $user->getEmail(),
        'nom' => $user->getNom(),
        'prenom' => $user->getPrenom(),
        'numtel' => $user->getNumtel(),
    );
    // Update corresponding table based on user type
    $type = $user->getType();
    if ($type === "client") {
        $client = $em->getRepository(Client::class)->findOneBy(["user" => $user]);

        if (!empty($nom)) {
            $client->setNom($nom);
        }

        if (!empty($email)) {
            $client->SetEmail($email);
        }

        if (!empty($prenom)) {
            $client->setPrenom($prenom); 
        }

        if (!empty($numtel)) {
            $client->setNumtel($numtel);
        }

        try {
            $em->persist($client);
            $em->flush();
        } catch (\Exception $ex) {
            return new Response("fail: ".$ex->getMessage());
        }
    } else if ($type === "livreur") {
        $livreur = $em->getRepository(Livreur::class)->findOneBy(["user" => $user]);

        if (!empty($nom)) {
            $livreur->setNom($nom);
        }

        if (!empty($email)) {
            $livreur->SetEmail($email);
        }

        if (!empty($prenom)) {
            $livreur->setPrenom($prenom); 
        }

        if (!empty($numtel)) {
            $livreur->setNumtel($numtel);
        }

        try {
            $em->persist($livreur);
            $em->flush();
        } catch (\Exception $ex) {
            return new Response("fail: ".$ex->getMessage());
        }
    } else if ($type === "partenaire") {
        $partenaire = $em->getRepository(Partenaire::class)->findOneBy(["user" => $user]);

        if (!empty($nom)) {
            $partenaire->setNom($nom);
        }

        if (!empty($email)) {
            $partenaire->SetEmail($email);
        }

        if (!empty($numtel)) {
            $partenaire->setNumtel($numtel);
        }

        try {
            $em->persist($partenaire);
            $em->flush();
        } catch (\Exception $ex) {
            return new Response("fail: ".$ex->getMessage());
        }

    return new JsonResponse("Success", 200);
}

}





#[Route('histmobile/{email}', name: 'app_hist_mobile')]
public function historiqueLivraison(Security $security)
{
    // Récupération de l'utilisateur connecté
    $user = $security->getUser();
    
    // Récupération de l'id_client associé à l'utilisateur
    $entityManager = $this->getDoctrine()->getManager();
    $client = $entityManager->getRepository(Client::class)->findOneBy(['email' => $user->getEmail()]);
    $id_client = $client->getId();

    // Récupération des colis livrés associés au client
    $colis = $entityManager->createQuery(
        'SELECT c
        FROM App\Entity\Colis c
        JOIN c.livreur l
        WHERE c.id_client = :id_client AND c.etat_colis = :etat_colis'
    )
    ->setParameter('id_client', $id_client)
    ->setParameter('etat_colis', 'livré')
    ->getResult();
    
    // Construction de l'objet JSON à partir des colis récupérés
    $jsonColis = [];
    foreach ($colis as $c) {
        $jsonColis[] = [
            'ref' => $c->getRef(),
            'depart' => $c->getDepart(),
            'destination' => $c->getDestination(),
            'poids' => $c->getPoids(),
            'prix' => $c->getPrix(),
            'nom' => $c->getLivreur()->getNom(),
            'email' => $c->getLivreur()->getEmail(),
        ];
    }

    // Retourne les résultats en format JSON
    $response = new JsonResponse($jsonColis);
    return $response;
}






}