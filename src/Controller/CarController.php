<?php

namespace App\Controller;

use App\Entity\Car;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CarController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function index(): Response
    {
        $cars = $this->entityManager->getRepository(Car::class)->findAll();
        
        return $this->render('car/index.html.twig', [
            'cars' => $cars
        ]);
    }

    public function add(Request $request): Response
    {
        $car = new Car();
        $car->setBrand($request->request->get('brand'));
        $car->setModel($request->request->get('model'));
        $car->setYear((int) $request->request->get('year'));

        $this->entityManager->persist($car);
        $this->entityManager->flush();

        return $this->redirectToRoute('cars_index');
    }

    public function edit(Request $request, int $id): Response
    {
        $car = $this->entityManager->getRepository(Car::class)->find($id);
        
        if (!$car) {
            throw $this->createNotFoundException('Car not found');
        }

        $car->setBrand($request->request->get('brand'));
        $car->setModel($request->request->get('model'));
        $car->setYear((int) $request->request->get('year'));

        $this->entityManager->flush();

        return $this->redirectToRoute('cars_index');
    }

    public function delete(int $id): Response
    {
        $car = $this->entityManager->getRepository(Car::class)->find($id);
        
        if (!$car) {
            throw $this->createNotFoundException('Car not found');
        }

        $this->entityManager->remove($car);
        $this->entityManager->flush();

        return $this->redirectToRoute('cars_index');
    }

    public function truncate(): Response
    {
        $cars = $this->entityManager->getRepository(Car::class)->findAll();
        foreach ($cars as $car) {
            $this->entityManager->remove($car);
        }
        $this->entityManager->flush();

        return $this->redirectToRoute('cars_index');
    }
}