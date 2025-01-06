<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\CategoryFormType;
use App\Entity\Category;
use App\Repository\CategoryRepository;
use Symfony\Component\HttpFoundation\Request;



class CategoryController extends AbstractController
{
    #[Route('/admin/category', name: 'app_category')]
    public function index(CategoryRepository $categoryRepository): Response
    {
        $categories = $categoryRepository->findAll();
        return $this->render('category/index.html.twig', [
            'categories' => $categories, 
        ]);
    }
    #[Route('/admin/category/new', name: 'app_category_new')]
    public function addCategory(EntityManagerInterface $entityManager, Request $request ): Response
    {
        $category = new Category();

        $form = $this->createForm(CategoryFormType::class,$category );
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $entityManager->persist($category);
            $entityManager->flush();

            $this->addFlash('success', 'votre catégorie a été ajoutée avec succès');

            return $this->redirectToRoute('app_category');
        }

        return $this->render('category/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/admin/category/{id}/update', name: 'app_category_update')]
    public function update(EntityManagerInterface $entityManager, Request $request, Category $category): Response
    {
        $form = $this->createForm(CategoryFormType::class,$category );
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $entityManager->flush();

            $this->addFlash('success', 'votre catégorie a été modifiée avec succès');

            return $this->redirectToRoute('app_category');
        }

        return $this->render('category/update.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/admin/category/{id}/delete', name: 'app_category_delete')]
    public function delete(EntityManagerInterface $entityManager, Category $category): Response
    {
        $entityManager->remove($category);
        $entityManager->flush();

        $this->addFlash('danger', 'votre catégorie a été supprimée avec succès');

        return $this->redirectToRoute('app_category');
    }
}
