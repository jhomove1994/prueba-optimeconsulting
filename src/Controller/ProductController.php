<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductController extends AbstractController
{

    #[Route('/product/create', name: 'product.create')]
    public function create(Request $request, ValidatorInterface $validator): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $errors = $validator->validate($product);
            if (count($errors) > 0) {
                return $this->render('category/create.html.twig', [
                    'controller_name' => 'ProductController',
                    'form' => $form->createView(),
                    'errors' => $errors
                ]);
            }
            $em = $this->getDoctrine()->getManager();
            $em->persist($product);
            $em->flush();
            $this->addFlash('Success', 'Producto creado correctamente');
            return $this->redirectToRoute('product.index');
        }
        return $this->render('product/create.html.twig', [
            'controller_name' => 'ProductController',
            'form' => $form->createView(),
            'errors' => ''
        ]);
    }

    #[Route('/product', name: 'product.index')]
    public function index(PaginatorInterface $paginator, Request $request): Response
    {
        $query = $this->getDoctrine()->getManager()->getRepository(Product::class)->findByPaginate();
        
        $products = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            2 /*limit per page*/
        );
        return $this->render('product/index.html.twig', [
            'products' => $products
        ]);
    }

    #[Route('/product/{id}/edit', name: 'product.edit')]
    public function edit(Request $request, int $id, ValidatorInterface $validator): Response
    {
        $product = $this->getDoctrine()->getRepository(Product::class)->find($id);

        if (!$product) {
            $this->addFlash('Error', 'Producto no encontrado');
            return $this->redirectToRoute('product.index');
        }

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $errors = $validator->validate($product);
            if (count($errors) > 0) {
                return $this->render('category/edit.html.twig', [
                    'controller_name' => 'ProductController',
                    'form' => $form->createView(),
                    'errors' => $errors
                ]);
            }
            $em = $this->getDoctrine()->getManager();
            $em->persist($product);
            $em->flush();
            $this->addFlash('Success', 'Producto actualizado correctamente');
            return $this->redirectToRoute('product.index');
        }
        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
            'errors' => ''
        ]);
    }

    #[Route('/product/{id}/delete', name: 'product.delete')]
    public function delete(int $id): Response
    {
        $product = $this->getDoctrine()->getRepository(Product::class)->find($id);

        if (!$product) {
            $this->addFlash('Error', 'Producto no encontrado');
            return $this->redirectToRoute('product.index');
        }
        
        $em = $this->getDoctrine()->getManager();
        $em->remove($product);
        $em->flush();

        $this->addFlash('Success', 'Producto eliminado correctamente');
        return $this->redirectToRoute('product.index');
    }

    /**
     * @Route("product/export",  name="product.export")
     */
    public function export()
    {
        $spreadsheet = new Spreadsheet();

        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setTitle('Product List');

        $sheet->getCell('A1')->setValue('Id');
        $sheet->getCell('B1')->setValue('Code');
        $sheet->getCell('C1')->setValue('Name');
        $sheet->getCell('D1')->setValue('Description');
        $sheet->getCell('E1')->setValue('Brand');
        $sheet->getCell('F1')->setValue('Category');
        $sheet->getCell('G1')->setValue('Price');
        $sheet->getCell('H1')->setValue('CreatedAt');

        // Increase row cursor after header write
        $sheet->fromArray($this->getData(),null, 'A2', true);
        

        $writer = new Xlsx($spreadsheet);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'. urlencode('excel.xlsx').'"');
        $writer->save('php://output');
        return $this->redirectToRoute('product.index');
    }

    private function getData(): array
    {
        $list = [];
        $products = $this->getDoctrine()->getManager()->getRepository(Product::class)->findAll();

        foreach ($products as $product) {
            $list[] = [
                $product->getId(),
                $product->getCode(),
                $product->getName(),
                $product->getDescription(),
                $product->getBrand(),
                $product->getCategory()->getName(),
                $product->getPrice(),
                $product->getCreatedAt()->format('Y-m-d')
            ];
        }
        return $list;
    }
}
