<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Order;
use App\Entity\Product;
use App\Form\OrderType;
use App\Repository\ProductRepository;
use App\Entity\City;
use App\Entity\OrderProducts;
use App\Service\Cart;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\ConComponent\Mailer\Exception\TransportExceptionInterface;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class OrderController extends AbstractController
{
    public function __construct(private MailerInterface $mailer){}
    
    #[Route('/order', name: 'app_order')]
    public function index(Request $request, 
        ProductRepository $productRepository,
        SessionInterface $session,
        EntityManagerInterface $entityManager,
        Cart $cart
    ): Response
    {
        $data = $cart->getCart($session);

        $order = new Order();
        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            if ($order->isPayOnDelivery()){

                if (!empty($data['total'])){

                    $order->setTotalPrice($data['total']);
                    $order->setCreatedAt(new \DateTimeImmutable());
                    $entityManager->persist($order);
                    $entityManager->flush();

                    foreach($data['cart'] as $value){
                        $orderProduct = new OrderProducts();
                        $orderProduct->setOrder($order);
                        $orderProduct->setProduct($value['product']);
                        $orderProduct->setQte($value['quantity']);
                        $entityManager->persist($orderProduct);
                        $entityManager->flush();                    
                    }
                } 
                $session->set('cart', []);

                $html = $this->renderView('mail/orderConfirm.html.twig',[
                    'order'=>$order
                ]);

                $email = (new Email())
                ->from('arthouse@gmail.com')
                ->to($order->getEmail())
                ->subject('Confirmation de reception de la commande')
                ->html($html);

                $this->mailer->send($email);
                return $this->redirectToRoute('order-ok-message');
            }
        }

        return $this->render('order/index.html.twig', [
            'form' => $form->createView(),
            'total' => $data['total']
        ]);
    }

    #[Route('/editor/order', name: 'app_orders_show')]
    public function getAllOrder(OrderRepository $orderRepository, Request $request, PaginatorInterface $paginator):Response
    {
        $data = $orderRepository->findBy([], ['id' => 'DESC']);
        $order = $paginator->paginate(
            $data,
            $request->query->getInt('page', 1),
            1
        );
        return $this->render('order/order.html.twig',[
           "orders"=>$order 
        ]);
    }

    #[Route('/editor/order/{id}/is-completed/update', name: 'app_orders_is_completed_updated')]
    public function isCompletedUpdate($id, OrderRepository $orderRepository, EntityManagerInterface $entityManager):Response
    {
       $order = $orderRepository->find($id);
       $order->setIsCompleted(true);
       $entityManager->flush();
       $this->addFlash('success','modification affectué');
       return $this->redirectToRoute('app_orders_show'); 
    }

    #[Route('/editor/order/{id}/remove', name: 'app_orders_remove')]
    public function removeOrder(Order $order, EntityManagerInterface $entityManager):Response
    {
        $entityManager->remove($order);
        $entityManager->flush();
        $this->addFlash('danger','Votre commande à été supprimée');
        return $this->redirectToRoute('app_orders_show');
    }

    #[Route('/odrer-ok-message', name: 'order-ok-message')]
    public function orderMessage():Response
    {
        return $this->render('order/order_message.twig');
    }

    #[Route('/city/{id}/shipping/cost', name: 'app_city_shipping_cost')]
    public function cityShippingCost(City $city): Response
    {
        $cityShippingPrice = $city->getShippingCost();

        return new Response(json_encode(['status'=>200, "message"=>'on', 'content'=>$cityShippingPrice]));
    }
    
}
