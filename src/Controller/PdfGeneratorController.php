<?php
 
namespace App\Controller;
use App\Entity\Livreur;
use App\Entity\Partenaire;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\LivreurRepository;
use Symfony\Component\HttpFoundation\Request;
use Dompdf\Dompdf;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class PdfGeneratorController extends AbstractController
{
    #[Route('/pdf/generator', name: 'app_pdf_generator')]
    public function index(Request $request, LivreurRepository $livreurRepository): Response
    {
        // Retrieve the current user's Partenaire object from the session
        $partenaire = $this->getUser();


        // Find the Livreur objects associated with the current Partenaire
        $livreurs = $livreurRepository->findBy(['id_partenaire' => $partenaire->getId()]);

        // Render the PDF template with the Livreur data
        $data = [
            'livreurs' => $livreurs,
        ];
        $html = $this->renderView('pdf_generator/index.html.twig', $data);

        // Generate the PDF file and return it as a response
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->render();
        $output = $dompdf->output();
        if ($output) {
            return new Response($output, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="resume.pdf"',
            ]);
        } else {
            echo "An error occurred while generating the PDF file.";
            exit(1);
        }
    }


}