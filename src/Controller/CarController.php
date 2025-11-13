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
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(Request $request, ManagerRegistry $doctrine): Response
    {
        $em = $doctrine->getManager();
        $repo = $em->getRepository(Car::class);

        $cars = $repo->findAll();

        return $this->render('car/index.html.twig', [
            'cars' => $cars,
        ]);
    }

    #[Route('/add', name: 'add', methods: ['POST'])]
    public function add(Request $request, ManagerRegistry $doctrine): Response
    {
        $em = $doctrine->getManager();

        $brand = (string) $request->request->get('brand', '');
        $model = (string) $request->request->get('model', '');
        $year = (int) $request->request->get('year', 0);

        $car = new Car();
        $car->setBrand($brand);
        $car->setModel($model);
        $car->setYear($year);

        $em->persist($car);
        $em->flush();

        return $this->redirectToRoute('cars_index');
    }
    /* 
        #[Route('/add/{brand}/{model}/{year}', name: 'add', methods: ['GET'])]
        public function add(string $brand, string $model, int $year, ManagerRegistry $doctrine): Response
        {
            // sanitize & validate ...
            $em = $doctrine->getManager();
            $car = new Car();
            $car->setBrand($brand);
            $car->setModel($model);
            $car->setYear($year);
            $em->persist($car);
            $em->flush();

            return $this->redirectToRoute('cars_index');
        }

    */
    #[Route('/edit/{id}', name: 'edit', methods: ['POST'])]
    public function edit(Request $request, ManagerRegistry $doctrine, int $id): Response
    {
        $em = $doctrine->getManager();
        $repo = $em->getRepository(Car::class);

        $car = $repo->find($id);
        if ($car instanceof Car) {
            $car->setBrand((string) $request->request->get('brand', ''));
            $car->setModel((string) $request->request->get('model', ''));
            $car->setYear((int) $request->request->get('year', 0));
            $em->flush();
        }

        return $this->redirectToRoute('cars_index');
    }

    #[Route('/delete/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, ManagerRegistry $doctrine, int $id): Response
    {
        $em = $doctrine->getManager();
        $repo = $em->getRepository(Car::class);

        $car = $repo->find($id);
        if ($car instanceof Car) {
            $em->remove($car);
            $em->flush();
        }

        return $this->redirectToRoute('cars_index');
    }

    #[Route('/dropAll', name: 'drop_all', methods: ['POST'])]
    public function dropAll(Request $request, ManagerRegistry $doctrine): Response
    {
        $em = $doctrine->getManager();
        $em->createQuery('DELETE FROM App\\Entity\\Car c')->execute();

        return $this->redirectToRoute('cars_index');
    }

    #[Route('/exampleData', name: 'seed', methods: ['POST'])]
    public function seed(Request $request, ManagerRegistry $doctrine): Response
    {
        $em = $doctrine->getManager();

        // remove everything first
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
            $car->setYear($data[2] + ($i % 3));
            $em->persist($car);
        }

        $em->flush();

        return $this->redirectToRoute('cars_index');
    }
}
