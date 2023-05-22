<?php

namespace App\Controller;

use Monolog\DateTimeImmutable;
use PDO;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Reclamation;
use App\Form\ReclamationType;
use App\Repository\ReclamationRepository;
use Symfony\Component\Validator\Constraints\DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;



class ReclamationController extends AbstractController
{

    #[Route('/reclamation', name: 'reclamation_index', methods: ['GET'])]
    public function index(): Response
    {
        $user = $this->getUser();
        $userID = $user->getId();
        return $this->render('reclamation/index.html.twig',[
            'userId' => $userID
        ]);
    }


    #[Route('/ShowUserReclamation/{id}', name: 'app_user_reclamation', methods: ['GET'])]
    public function showUserReclamation(int $id, ReclamationRepository $reclamationRepository, PaginatorInterface $paginator, Request $request, MailerInterface $mailer): Response
{
    $user = $this->getUser();
    $userID = $user->getId();

    // Get the requested sort and direction
    $sortDirection = $request->query->get('sort');
    $sortBy = $request->query->get('sortBy');

    // Build the query
    $qb = $reclamationRepository->createQueryBuilder('r')
        ->where('r.id_client = :id')
        ->setParameter('id', $id);

    // Add ordering if requested
    if ($sortBy && in_array($sortBy, ['stars', 'createdAt'])) {
        $sortDirection = in_array($sortDirection, ['asc', 'desc']) ? $sortDirection : 'asc';
        $qb->orderBy('r.' . $sortBy, $sortDirection);
    }

    // Get the paginated results
    $reclamations = $qb->getQuery()->getResult();
    $pagination = $paginator->paginate(
        $reclamations,
        $request->query->getInt('page', 1),
        5
    );

    return $this->render('client/clientReclamation.html.twig', [
        'reclamation' => $pagination,
        'userId' => $userID
    ]);
}


    #[Route('/ShowLivreurReclamation/{id}', name: 'app_livreur_reclamation', methods: ['GET'])]
    public function showLivreurReclamation(int $id,ReclamationRepository $reclamationRepository, PaginatorInterface $paginator, Request $request): Response
    {   
        $user = $this->getUser();
        $userID = $user->getId();
        try {
            $sql = 'SELECT *
            FROM reclamation
            WHERE ref IN (
              SELECT ref
              FROM colis
              WHERE id_livreur IN (
                SELECT id
                FROM livreur
                WHERE user_id = :id_livreur
              )
            )';
            $pdo = new PDO('mysql:host=localhost;dbname=taktakv;charset=utf8mb4', 'root', '');
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id_livreur', $id, PDO::PARAM_INT);
            $stmt->execute();
            $reclamations = $stmt->fetchAll();
        } catch (\PDOException $e) {
            throw new \PDOException($e->getMessage(), (int) $e->getCode());
        }

        $pagination = $paginator->paginate(
            $reclamations,
            $request->query->getInt('page', 1), // Current page number
            3 // Number of items per page
        );

        return $this->render('livreur/showLivreurReclamation.html.twig', [
            'reclamations' => $pagination,
            'userId' => $userID
        ]);
    }


    #[Route('/ShowPartenaireReclamation/{id}', name: 'app_partenaire_reclamation', methods: ['GET'])]
    public function showPartenaireReclamation(int $id, PaginatorInterface $paginator, Request $request): Response
    {
        $user = $this->getUser();
        $userID = $user->getId();
        try {
            $sql = 'SELECT *
            FROM reclamation
            WHERE ref IN (
              SELECT ref
              FROM colis
              WHERE id_partenaire IN (
                SELECT id
                FROM partenaire
                WHERE user_id = :id_partenaire
              )
            );';
            $pdo = new PDO('mysql:host=localhost;dbname=taktakv;charset=utf8mb4', 'root', '');

            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id_partenaire', $id, PDO::PARAM_INT);
            $stmt->execute();
            $reclamations = $stmt->fetchAll();
        } catch (\PDOException $e) {
            throw new \PDOException($e->getMessage(), (int) $e->getCode());
        }
    
        // Paginate query results
        $pagination = $paginator->paginate(
            $reclamations,
            $request->query->getInt('page', 1), // Current page number
            3 // Number of items per page
        );
    
        return $this->render('partenaire/showPartenaireReclamation.html.twig', [
            'reclamations' => $pagination,
            'userId' => $userID
        ]);
    }


    #[Route('/Feedback', name: 'show_feedback_reclamation', methods: ['GET'])]
    public function showFeedback(ReclamationRepository $reclamationRepository, Request $request): Response
    {
        $user = $this->getUser();
        $userID = $user->getId();
        $sortDirection = $request->query->get('sort');
        $sortBy = $request->query->get('sortBy');
        $qb = $reclamationRepository->createQueryBuilder('r');

        if ($sortBy && in_array($sortBy, ['stars', 'createdAt'])) {
            $sortDirection = in_array($sortDirection, ['asc', 'desc']) ? $sortDirection : 'asc';
            $qb->orderBy('r.' . $sortBy, $sortDirection);
        }
        $reclamations = $qb->getQuery()->getResult();
    
        return $this->render('reclamation/feedback.html.twig', [
            'feedbacks' => $reclamations,
            'userId' => $userID
        ]);
    }

    #[Route('/userReclamation/delete/{id}', name: 'app_delete_reclamation', methods: ['DELETE','POST','GET'])]
    public function delete(Reclamation $reclamation, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $userID = $user->getId();
        $entityManager->remove($reclamation);
        $entityManager->flush();
    
    
        return $this->redirectToRoute('app_user_reclamation', ['id' => $userID]);
    }

    #[Route('/ajouterFeedback/{id}', name: 'app_post_feedback', methods: ['GET','POST'])]
    public function postFeedback(int $id, Request $request ,EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $userID = $user->getId();

            $reclamation = new Reclamation();

            $form = $this->createForm(ReclamationType::class, $reclamation);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $reclamation->setIdClient($userID);
                $now = new DateTimeImmutable(false);    
                $reclamation->setDate($now);
                $entityManager->persist($reclamation);
                $entityManager->flush();

                
                $transport = Transport::fromDsn('smtp://chebbi.mohamed.1@esprit.tn:223JMT1915@smtp.gmail.com:587');
                $mailer = new Mailer($transport);
                $email = (new Email());

                $email->from('email@gmail.com');

                // Set the "To address"
                $email->to('chebbim4@gmail.com');
                $email->subject('Noreply email');
                $email->text("Merci pour votre retour d'information précieux. Nous apprécions votre contribution !");
                $mailer->send($email);

                // redirect to success page
                return $this->redirectToRoute('app_user_reclamation', [
                    'id' => $userID,
                    'userId' => $userID
                ]);
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
            return $this->redirectToRoute('app_user_reclamation', ['id' => $userID]);
        }

        return $this->render('client/changeReclamation.html.twig', [
            'reclamation' => $reclamation,
            'edit_form' => $editForm->createView(),
            'userId' => $userID
        ]);
    }
}
