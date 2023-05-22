<?php

namespace App\Controller;

use App\Entity\Livreur;
use App\Entity\MoyenDeTransport;
use App\Entity\Partenaire;
use App\Form\MoyenDeTransportType;
use App\Repository\LivreurRepository;
use App\Repository\MoyenDeTransportRepository;
use App\Repository\PartenaireRepository;
use Doctrine\Persistence\ManagerRegistry;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelLow;
use Endroid\QrCode\Label\Font\NotoSans;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;



use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
     * @Route("/moyen_de_transport"))
     */

class MoyenDeTransportController extends AbstractController
{
    private $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function getDoctrine(): ManagerRegistry
    {
        return $this->doctrine;
    }
    /**
     * @Route("/index", name="app_moyen_de_transport_index")
     */
    public function index(Request $request, NormalizerInterface $normalizer, MoyenDeTransportRepository $moyenDeTransportRepository): Response
    {
        $marque = $request->query->get('marque');
        $sortByMarque = $request->query->get('sortByMarque');

        if ($marque) {
            $moyen_de_transports = $moyenDeTransportRepository->findByMarque($marque);
        } else {
            $moyen_de_transports = $moyenDeTransportRepository->findAll();
        }

        if ($sortByMarque) {
            usort($moyen_de_transports, function($a, $b) {
                return strcmp($a->getMarque(), $b->getMarque());
            });
        }

        $moyenDeTransportNormalized = $normalizer->normalize($moyen_de_transports, 'json', ['groups' => 'MoyenDeTransport']);
        $jsonData = json_encode($moyenDeTransportNormalized);

        return new Response($jsonData, 200, ['Content-Type' => 'application/json']);
    }

    #[Route('/Generator_{id}', name: 'app_qr_codes')]
    public function QrGenerator($id): Response
    {
        $writer = new PngWriter();
        $qrCode = QrCode::create(sprintf('http://127.0.0.1:8000/Generator_%s', $id))
            ->setEncoding(new Encoding('UTF-8'))
            ->setErrorCorrectionLevel(new ErrorCorrectionLevelLow())
            ->setSize(120)
            ->setMargin(0)
            ->setForegroundColor(new Color(0, 0, 0))
            ->setBackgroundColor(new Color(255, 255, 255));
        $label = Label::create('')->setFont(new NotoSans(8));

        $qrCodes = [];
        $qrCodes['simple'] = $writer->write(
            $qrCode,
            null,
            $label->setText('Simple')
        )->getDataUri();

        $qrCode->setForegroundColor(new Color(255, 0, 0));
        $qrCodes['changeColor'] = $writer->write(
            $qrCode,
            null,
            $label->setText('Color Change')
        )->getDataUri();

        $qrCode->setForegroundColor(new Color(0, 0, 0))->setBackgroundColor(new Color(255, 0, 0));
        $qrCodes['changeBgColor'] = $writer->write(
            $qrCode,
            null,
            $label->setText('Background Color Change')
        )->getDataUri();

        $qrCode->setSize(200)->setForegroundColor(new Color(0, 0, 0))->setBackgroundColor(new Color(255, 255, 255));


        return $this->render('qr_code_generator/index.html.twig', $qrCodes);
    }





    /**
     * @Route("/search", name="app_moyen_de_transport_search")
     */
    public function search(Request $request, MoyenDeTransportRepository $repository): Response
    {        $partenaire = $this->getUser();

        $marque = $request->query->get('marque');
        $sortByMarque = $request->query->get('sortByMarque');

        $moyenDeTransports = $repository->findByMarque($marque);
        if ($sortByMarque) {
            usort($moyenDeTransports, function($a, $b) {
                return strcmp($a->getMarque(), $b->getMarque());
            });
        }


        return $this->render('moyen_de_transport/index.html.twig', [
            'partenaire' => $partenaire ,
            'moyen_de_transports' => $moyenDeTransports,
            'sortByMarque' => $sortByMarque,
        ]);
    }
    /**
     * @Route("/filter", name="app_moyen_de_transport_filter")
     */
    public function filterMoyenDeTransport(Request $request, MoyenDeTransportRepository $moyenDeTransportRepository)
    {        $partenaire = $this->getUser();

        $sortByMarque = $request->query->get('sortByMarque');

        $type = $request->query->get('type');

        if ($type === 'voiture') {
            $moyenDeTransports = $moyenDeTransportRepository->findBy(['type' => 1]);
        } elseif ($type === 'camion') {
            $moyenDeTransports = $moyenDeTransportRepository->findBy(['type' => 0]);
        } else {
            $moyenDeTransports = $moyenDeTransportRepository->findAll();
        }
        if ($sortByMarque) {
            usort($moyenDeTransports, function($a, $b) {
                return strcmp($a->getMarque(), $b->getMarque());
            });
        }

        return $this->render('moyen_de_transport/index.html.twig', [
            'moyen_de_transports' => $moyenDeTransports,
            'partenaire' => $partenaire,
            'sortByMarque' => $sortByMarque,

        ]);
    }







    #[Route('/new', name: 'app_moyen_de_transport_new', methods: ['GET','POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager,NormalizerInterface $normalizer): Response
    {

        $marque = $request->query->get('marque');
        $type = $request->query->get('type');
        $matricule = $request->query->get('matricule');

        $moyenDeTransport = new MoyenDeTransport();
        $moyenDeTransport->setMarque($marque);
        $moyenDeTransport->setType($type);
        $moyenDeTransport->setMatricule($matricule);

        $entityManager->persist($moyenDeTransport);
        $entityManager->flush();

        $moyenDeTransportNormalized = $normalizer->normalize($moyenDeTransport, 'json', ['groups' => 'MoyenDeTransport']);
        $jsonData = json_encode($moyenDeTransportNormalized);

        return new Response($jsonData, 200, ['Content-Type' => 'application/json']);
    }


    #[Route('/mailling', name: 'app_mailling')]
    public function sendEmail(string $toEmail ,string $Marque, string $type, string $matricule): Response
    {

            $transport = Transport::fromDsn('smtp://mootez202@gmail.com:ppbifuhwieebcjbg@smtp.gmail.com:587?verify_peer=0');
            $mailer = new Mailer($transport);
            $email = (new Email());
            $email->from('mootez202@gmail.com');
            $email->to($toEmail);
        $email->subject(sprintf('%s %s assigné', $Marque, $type == '1' ? 'voiture' : 'camion'));
        $email->text(sprintf('Vous avez été assigné à un %s avec la plaque d\'immatriculation %s.', $Marque, $matricule));
        $email->html(sprintf('
<div style="background-color: #F5F5F5; padding: 20px; font-family: Arial, sans-serif;">
    <h1 style="color: #0073ff; font-size: 24px; font-weight: bold; text-align: center; margin-bottom: 30px;">Livreur assigné</h1>
    <div style="background-color: #fff; padding: 20px; border-radius: 10px;">
        <p style="color: #444; font-size: 18px; margin-bottom: 10px;">Vous avez été assigné à un %s avec la plaque dimmatriculation %s.</p>
        <p style="color: #444; font-size: 18px; margin-bottom: 10px;">Marque : %s</p>
        <p style="color: #444; font-size: 18px; margin-bottom: 10px;">Type : %s</p>
    </div>
    <div style="background-color: #0073ff; color: #fff; text-align: center; padding: 10px; margin-top: 30px; border-radius: 10px;">
        <p style="font-size: 14px; margin: 0;">Merci d utiliser notre service.</p>
    </div>
</div>
', $Marque, $matricule, $Marque, $type == '1' ? 'voiture' : 'camion'));

try {
    $mailer->send($email);
    die('<style> * { font-size: 50px; color: #fff; background-color: #4caf50; } </style><pre><h1>&#10004; Email envoyé avec succès !</h1></pre>');
} catch (TransportExceptionInterface $e) {
    die('<style>* { font-size: 50px; color: #fff; background-color: #f44336; }</style><pre><h1>&#10060; Erreur lors de l\'envoi de l\'email !</h1></pre>');
}

        }

    /**
     * @Route("/{id}/assign", name="app_moyen_de_transport_assign", methods={"POST"})
     */
    public function assignLivreur(Request $request,MoyenDeTransport $moyenDeTransport,EntityManagerInterface $entityManager): Response
    {
        $livreurId = $request->request->get('livreur_id');
        $livreur = $entityManager->getRepository(Livreur::class)->find($livreurId);


        if (!$livreur) {
            throw $this->createNotFoundException('Unable to find Livreur entity.');
        }

        $moyenDeTransport->setLivreur($livreur);

        $entityManager->flush();
        $toEmail = $livreur->getEmail();
        $Marque = $moyenDeTransport->getMarque();
        $type = $moyenDeTransport->getType();
        $matricule= $moyenDeTransport->getMatricule();
        $this->sendEmail($toEmail, $Marque, $type, $matricule);
        return $this->redirectToRoute('app_moyen_de_transport_index');
    }

    /**
     * @Route("/{id}", name="app_moyen_de_transport_show", methods={"GET", "POST"})
     */
    public function show(Request $request, MoyenDeTransport $moyenDeTransport, EntityManagerInterface $entityManager, LivreurRepository $livreurRepository, PartenaireRepository $partenaireRepository, SerializerInterface $serializer): Response
    {

        if ($request->getMethod() == 'POST' || $request->getMethod() == 'GET') {
            $marque = $request->query->get('marque', $moyenDeTransport->getMarque());
            $type = $request->query->get('type', $moyenDeTransport->getType());
            $matricule = $request->query->get('matricule', $moyenDeTransport->getMatricule());


            $moyenDeTransport->setMarque($marque);
            $moyenDeTransport->setType($type);
            $moyenDeTransport->setMatricule($matricule);

            if ($request->query->has('assign')) {
                $livreurId = $request->query->get('livreur_id');
                $livreur = $entityManager->getRepository(Livreur::class)->find($livreurId);
                $moyenDeTransport->setLivreur($livreur);
            }

            $entityManager->persist($moyenDeTransport);
            $entityManager->flush();
        }

        $moyenDeTransportNormalized = $serializer->normalize($moyenDeTransport, null, ['groups' => 'MoyenDeTransport']);
        $jsonData = json_encode($moyenDeTransportNormalized);

        return new Response($jsonData, 200, ['Content-Type' => 'application/json']);
    }







    /**
     * @Route("/delete/{id}", name="app_moyen_de_transport_delete", methods={"GET"})
     */
    public function delete(int $id, EntityManagerInterface $entityManager, MoyenDeTransportRepository $moyenDeTransportRepository): JsonResponse
    {
        $moyenDeTransport = $moyenDeTransportRepository->find($id);
        if($moyenDeTransport != null){
            $entityManager->remove($moyenDeTransport);
            $entityManager->flush();
            $formatted = "Moyen de transport a été supprimé avec succès";
            return new JsonResponse($formatted);
        }
        $formatted = "L'ID du Moyen De Transport est invalide";
        return new JsonResponse($formatted, Response::HTTP_BAD_REQUEST);
    }



}
