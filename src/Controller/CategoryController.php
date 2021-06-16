<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Product;
use App\Form\CategoryType;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CategoryController extends AbstractController
{
    #[Route('/category/create', name: 'category.create')]
    public function create(Request $request, ValidatorInterface $validator): Response
    {
        $category = new Category();

        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $errors = $validator->validate($category);
            if (count($errors) > 0) {
                return $this->render('category/create.html.twig', [
                    'controller_name' => 'CategoryController',
                    'form' => $form->createView(),
                    'errors' => $errors
                ]);
            }
            $em = $this->getDoctrine()->getManager();
            $em->persist($category);
            $em->flush();
            $this->addFlash('Success', 'Categoria creada correctamente');
            return $this->redirectToRoute('category.index');
        }

        return $this->render('category/create.html.twig', [
            'controller_name' => 'CategoryController',
            'form' => $form->createView(),
            'errors' => ''
        ]);
    }

    #[Route('/category', name: 'category.index')]
    public function index(PaginatorInterface $paginator, Request $request): Response
    {
        $em = $this->getDoctrine()->getManager();
        $query = $em->getRepository(Category::class)->findByPaginate();
        $categories = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );
        return $this->render('category/index.html.twig', [
            'categories' => $categories
        ]);
    }

    #[Route('/category/{id}/edit', name: 'category.edit')]
    public function edit(Request $request, int $id, ValidatorInterface $validator): Response
    {
        $category = $this->getDoctrine()->getRepository(Category::class)->find($id);

        if (!$category) {
            $this->addFlash('Error', 'Categoria no encontrada');
            return $this->redirectToRoute('category.index');
        }

        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $errors = $validator->validate($category);
            if (count($errors) > 0) {
                return $this->render('category/category.html.twig', [
                    'controller_name' => 'CategoryController',
                    'form' => $form->createView(),
                    'errors' => $errors
                ]);
            }
            $em = $this->getDoctrine()->getManager();
            $em->persist($category);
            $em->flush();
            $this->addFlash('Success', 'Categoria actualizada correctamente');
            return $this->redirectToRoute('category.index');
        }
        return $this->render('category/edit.html.twig', [
            'category' => $category,
            'form' => $form->createView(),
            'errors' => ''
        ]);
    }

    #[Route('/category/{id}/delete', name: 'category.delete')]
    public function delete(int $id): Response
    {
        $category = $this->getDoctrine()->getRepository(Category::class)->find($id);

        if (!$category) {
            $this->addFlash('Error', 'Categoria no encontrada');
            return $this->redirectToRoute('category.index');
        }

        $coincidences = $this->getDoctrine()->getRepository(Product::class)->findByCategory($category->getId());
        
        if(count($coincidences)) {
            $this->addFlash('Error', 'No se puede eliminar la categoria porque tiene productos asociados');
            return $this->redirectToRoute('category.index');
        }
        
        $em = $this->getDoctrine()->getManager();
        $em->remove($category);
        $em->flush();

        $this->addFlash('Success', 'Categoria eliminada correctamente');
        
        return $this->redirectToRoute('category.index');
    }
}
