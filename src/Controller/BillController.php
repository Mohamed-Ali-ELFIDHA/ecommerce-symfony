<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\OrderRepository;

class BillController extends AbstractController
{
    #[Route('/editor/order/{id}/bill', name: 'app_bill')]
    public function index($id, OrderRepository $orderRepository): Response
    {
        $order = $orderRepository->find($id);
        return $this->render('bill/index.html.twig', [
            'order'=>$order
        ]);
    }
}
