<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Stock;
use App\Entity\Depot;
use App\Form\StockType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;

final class StockController extends AbstractController
{
    #[Route('/add_stock/{depotId}', name: 'app_add_stock')]
public function addStock(Request $request, EntityManagerInterface $entityManager, int $depotId): Response
{
    // Fetch the depot using the provided depotId
    $depot = $entityManager->getRepository(Depot::class)->find($depotId);

    if (!$depot) {
        throw $this->createNotFoundException('Depot not found');
    }

    // Create a new Stock entity and associate it with the depot
    $stock = new Stock();
    $stock->setDepot($depot);

    // Create form and bind stock object
    $form = $this->createForm(StockType::class, $stock);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->persist($stock);
        $entityManager->flush();

        $this->addFlash('success', 'Stock added successfully!');
        return $this->redirectToRoute('app_depot_associated_stocks', ['depotId' => $depot->getId()]);
    }

    return $this->render('stock/AddStock.html.twig', [
        'form' => $form->createView(),
        'depot' => $depot, // Pass depot info
    ]);
}
    #[Route('/stocks/{depotId}', name: 'app_depot_associated_stocks')]
    public function stockList(EntityManagerInterface $entityManager, int $depotId): Response
    {
        // Fetch the depot using the provided depotId
        $depot = $entityManager->getRepository(Depot::class)->find($depotId);

        if (!$depot) {
            throw $this->createNotFoundException('Depot not found');
        }

        // Fetch only stocks related to this depot
        $stocks = $entityManager->getRepository(Stock::class)->findBy(['depot' => $depot]);

        return $this->render('Back/AdminDepot/listStocks.html.twig', [
            'stocks' => $stocks,
            'depot' => $depot, // Pass depot info to the template
        ]);
    }
    #[Route('/stock/delete/{id}', name: 'app_stock_delete', methods: ['POST'])]
public function deleteStock(int $id, EntityManagerInterface $entityManager): Response
{
    $stock = $entityManager->getRepository(Stock::class)->find($id);

    if (!$stock) {
        $this->addFlash('danger', 'Stock not found.');
        return $this->redirectToRoute('app_stock_list');
    }

    $entityManager->remove($stock);
    $entityManager->flush();
    $this->addFlash('success', 'Stock deleted successfully.');

    return $this->redirectToRoute('app_depot_associated_stocks', ['depotId' => $stock->getDepot()->getId()]);
}
#[Route('/stock/update/{id}', name: 'app_stock_update')]
public function updateStock(int $id, Request $request, EntityManagerInterface $entityManager): Response
{
    $stock = $entityManager->getRepository(Stock::class)->find($id);

    if (!$stock) {
        $this->addFlash('danger', 'Stock not found.');
        return $this->redirectToRoute('app_depot_associated_stocks', ['depotId' => $request->query->get('depotId')]);
    }

    // Get the Depot from the Stock
    $depot = $stock->getDepot();

    // Create form WITHOUT a Depot field (depot is already set in stock)
    $form = $this->createForm(StockType::class, $stock);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->flush(); // last_update_date is auto-set by @PreUpdate
        $this->addFlash('success', 'Stock updated successfully.');
        return $this->redirectToRoute('app_depot_associated_stocks', ['depotId' => $depot->getId()]);
    }

    return $this->render('stock/updateStock.html.twig', [
        'form' => $form->createView(),
        'stock' => $stock,
        'depot' => $depot,
    ]);
}


}
