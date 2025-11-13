<?php

namespace App\Controller;

use App\Entity\Car;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/cars', name: 'cars_')]
class CarController extends AbstractController
{

    #[Route('/add')]
    public function add(Request $request, ManagerRegistry $doctrine): Response
    {



        dd('xxxxxxxxxxxxxxxxxxx');

        return $this->render('car/index.html.twig', []);
    }

    #[Route('/')]
    public function index(Request $request, ManagerRegistry $doctrine): Response
    {
        $em = $doctrine->getManager();
        $repo = $em->getRepository(Car::class);

        $cars = $repo->findAll();

        return $this->render('car/index.html.twig', [
            'cars' => $cars,
        ]);
    }

    public function index_oryginal(Request $request, ManagerRegistry $doctrine): Response
    {
        $em = $doctrine->getManager();
        $repo = $em->getRepository(Car::class);

        if ($request->isMethod('POST')) {
            // ======================================== Funkcja Add
            if ($request->request->has('add')) {
                $car = new Car();
                $car->setBrand((string) $request->request->get('brand', ''));
                $car->setModel((string) $request->request->get('model', ''));
                $car->setYear((int) $request->request->get('year', 0));
                $em->persist($car);
                $em->flush();

                return $this->redirectToRoute('cars_index');
            }

            // ======================================== Funkcja Edit    
            if ($request->request->has('edit')) {
                $id = $request->request->get('id');
                $car = $repo->find($id);
                if ($car instanceof Car) {
                    $car->setBrand((string) $request->request->get('brand', ''));
                    $car->setModel((string) $request->request->get('model', ''));
                    $car->setYear((int) $request->request->get('year', 0));
                    $em->flush();
                }

                return $this->redirectToRoute('cars_index');
            }

            // ======================================= Funkcja Delete
            if ($request->request->has('delete')) {
                $id = $request->request->get('id');
                $car = $repo->find($id);
                if ($car instanceof Car) {
                    $em->remove($car);
                    $em->flush();
                }

                return $this->redirectToRoute('cars_index');
            }

            // ======================================== Funkcja Wywalenia wszystkiego
            if ($request->request->has('drop_all')) {
                $em->createQuery('DELETE FROM App\\Entity\\Car c')->execute();
                return $this->redirectToRoute('cars_index');
            }

            // ======================================== Funkcja Wstawienia przykładowych danych (10 samochodów)
            if ($request->request->has('seed')) {
                // usuń wszystko najpierw
                $em->createQuery('DELETE FROM App\\Entity\\Car c')->execute();

                $sample = [
                    ['Toyota', 'Corolla', 2010],
                    ['Ford', 'Focus', 2012],
                    ['Honda', 'Civic', 2015],
                    ['Volkswagen', 'Golf', 2013],
                    ['BMW', '320i', 2014],
                    ['Audi', 'A4', 2016],
                    ['Mercedes', 'C200', 2011],
                    ['Opel', 'Astra', 2009],
                    ['Skoda', 'Octavia', 2017],
                    ['Renault', 'Clio', 2008],
                ];

                for ($i = 0; $i < 10; $i++) {
                    $data = $sample[$i % count($sample)];
                    $car = new Car();
                    $car->setBrand($data[0]);
                    $car->setModel($data[1]);
                    // add a small variation on year
                    $car->setYear($data[2] + ($i % 3));
                    $em->persist($car);
                }

                $em->flush();

                return $this->redirectToRoute('cars_index');
            }
        }

        $cars = $repo->findAll();

        return $this->render('car/index.html.twig', [
            'cars' => $cars,
        ]);
    }
}
