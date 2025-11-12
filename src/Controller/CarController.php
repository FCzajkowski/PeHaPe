<?php

namespace App\Controller;

use App\Entity\Car;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CarController extends AbstractController
{
    public function index(Request $request, ManagerRegistry $doctrine): Response
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
        }

        $cars = $repo->findAll();

        return $this->render('car/index.html.twig', [
            'cars' => $cars,
        ]);
    }
}