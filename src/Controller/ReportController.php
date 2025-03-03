<?php
namespace App\Controller;

use App\Service\ReportService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ReclamationRepository; 
use App\Entity\Remboursement;
use Twig\Environment;
use App\Entity\Reclamation;
use PhpOffice\PhpWord\PhpWord;
use App\Repository\RemboursementRepository; 
use PhpOffice\PhpWord\IOFactory;

class ReportController extends AbstractController
{
    private ReportService $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    #[Route('/api/reports', name: 'reclamation_reports')]
    public function reports(Request $request): Response
    {
        $locale = $request->getSession()->get('_locale', $request->getLocale());
        $request->getSession()->set('_locale', $locale);

        // Générer le rapport avec toutes les données nécessaires
        $reportData = $this->reportService->generateReport();

        // Définir la variable rapportTexte ici
        $rapportTexte = "Here is a summary of the report for the current period."; // Exemple de texte pour rapport

        // Rendre la vue avec toutes les données, y compris rapportTexte
        return $this->render('report/index.html.twig', [
            'reclamations' => $reportData['reclamations'],
            'remboursements' => $reportData['remboursements'],
            'rapportTexte' => $rapportTexte, // Passer la variable rapportTexte
            'totalReclamations' => $reportData['totalReclamations'],
            'totalRemboursements' => $reportData['totalRemboursements'],
            'montantMin' => $reportData['montantMin'],
            'montantMax' => $reportData['montantMax'],
        ]);
    }
   // Assurez-vous que vous avez bien ce namespace pour RemboursementRepository
    
   #[Route('/admin/download-report-word', name: 'admin_download_report_word')]
   public function downloadReportWord(Environment $twig): Response
   {
       // Générer le rapport avec toutes les données nécessaires
       $reportData = $this->reportService->generateReport();
   
       // Créer un document Word
       $phpWord = new PhpWord();
       $section = $phpWord->addSection();
   
       // Ajouter le logo au début du document
       $logoPath = $_SERVER['DOCUMENT_ROOT'] . '/img/logo.png'; // Si l'image est dans public/img
       if (file_exists($logoPath)) {
           $section->addImage($logoPath, [
               'width' => 100, // Largeur de l'image
               'height' => 100, // Hauteur de l'image
               'align' => 'center'
           ]);
       }
   
       // Ajouter un titre principal avec un style personnalisé
       $titleStyle = ['name' => 'Arial', 'size' => 16, 'bold' => true, 'color' => '1F4E79']; // Bleu foncé
       $section->addText('Report Summary', $titleStyle);
   
       // Ajouter le texte d'introduction
       $introStyle = ['name' => 'Arial', 'size' => 12, 'italic' => true, 'color' => '000000']; // Texte normal
       $section->addText('Here is a summary of the report for the current period:', $introStyle);
   
       // Ajouter les réclamations avec un titre coloré (couleur complémentaire ou basée sur votre logo)
       $section->addText('Reclamations:', ['bold' => true, 'size' => 12, 'color' => '5D6A92']); // Bleu grisé (couleur complémentaire)
   
       foreach ($reportData['reclamations'] as $reclamationId) {
           // Texte de réclamation en noir
           $section->addText("Claim ID: {$reclamationId}", ['italic' => true, 'size' => 10, 'color' => '000000']);
       }
   
       // Ajouter les remboursements avec un titre coloré
       $section->addText('Remboursements:', ['bold' => true, 'size' => 12, 'color' => '5D6A92']); // Bleu grisé (couleur complémentaire)
   
       foreach ($reportData['remboursements'] as $remboursement) {
           // Texte de remboursement en noir
           $section->addText(
               "Refund ID: {$remboursement->getIdRemboursement()} - Amount: {$remboursement->getMontant()} - Mode: {$remboursement->getModeRemboursement()}", 
               ['italic' => true, 'size' => 10, 'color' => '000000']
           );
       }
   
       // Ajouter un résumé avec des totaux (en noir)
       $section->addText('Summary:', ['bold' => true, 'size' => 12, 'color' => '5D6A92']); // Bleu grisé (couleur complémentaire)
       $section->addText(
           "Total Reclamations: {$reportData['totalReclamations']}\nTotal Remboursements: {$reportData['totalRemboursements']}",
           ['size' => 10, 'color' => '000000']
       );
   
       // Définir le nom du fichier et l'écrire dans un fichier temporaire
       $fileName = 'report.docx';
       $tempFile = tempnam(sys_get_temp_dir(), 'word');
       $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
       $objWriter->save($tempFile);
   
       // Retourner le fichier généré en réponse
       return new Response(file_get_contents($tempFile), 200, [
           'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
           'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
       ]);
   }
   
   
}


