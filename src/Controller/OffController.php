<?php

namespace App\Controller;

use App\Entity\Offers;
use App\Entity\User;
use App\Form\OffersType;
use App\Repository\OffersRepository;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Component\Pager\PaginatorInterface;



class OffController extends AbstractController
{
    #[Route('/user/homecompany/{email}', name: 'homecompany')]
    public function homeFreelancer($email, UserRepository $userRepository): Response
    {
        $user = $userRepository->findOneBy(['email' => $email]);

        if (!$user) {
            throw $this->createNotFoundException('User not found for email ' . $email);
        }

        return $this->render('user/homecompany.html.twig', [
            'controller_name' => 'CVController',
            'userid' => $user,
        ]);
    }
    #[Route('/off', name: 'app_off')]
    public function index(): Response
    {
        return $this->render('off/index.html.twig', [
            'controller_name' => 'OffController',

        ]);
    }

    #[Route('/addformoff/{iduser}', name: 'addformoff')]
    public function addformoff($iduser, UserRepository $UserRepository, ManagerRegistry $managerRegistry, Request $req): Response
    {
        $dataid = $UserRepository->find($iduser);
        $em = $managerRegistry->getManager();
        $offer = new Offers();
        $offer->setUser($dataid);
        $form = $this->createForm(OffersType::class, $offer);
        $form->handleRequest($req);
        if ($form->isSubmitted() and $form->isValid()) { //if form is not empty

            $em->persist($offer);
            $em->flush();
            $offerid = $offer->getUser()->getId();
            $this->addFlash('success', 'Your offer has been added successfully.');
            return $this->redirectToRoute('show_offers', ['id' => $offerid]); //to show the offers of the user
        }

        return $this->render('user/off/off.html.twig', [
            'form' => $form->createView(),
            'userid' => $dataid,
        ]);
    }

    //specific user offers
    #[Route('/show_offers/{id}', name: 'show_offers')] //show offers of the user
    public function showOffers(OffersRepository $offersRepository, UserRepository $UserRepository, $id): Response
    {
        $user = $UserRepository->find($id);

        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        $offers = $offersRepository->findBy(['User' => $user]);

        return $this->render('user/off/Showoffuser.html.twig', [
            'offers' => $offers,
            'userid' => $user,
        ]);
    }


    #[Route('/editOffer/{id}', name: 'editOffer')]
    public function editOffer($id, OffersRepository $offerRepository, ManagerRegistry $ManagerRegistry, Request $Req): Response
    {
        $em = $ManagerRegistry->getManager();

        $offer = $offerRepository->find($id);
        if (!$offer) {
            throw $this->createNotFoundException('Offer not found');
        }
        $form = $this->createForm(OffersType::class, $offer);
        $form->handleRequest($Req);
        if ($form->isSubmitted() and $form->isValid()) { //
            $em->persist($offer);
            $em->flush();
            return $this->redirectToRoute('show_offers', ['id' => $offer->getUser()->getId()]);
        }
        return $this->render('user/off/editoffer.html.twig', [
            'form' => $form->createView(),
            'userid' => $offer->getUser(),


        ]);
    }


    #[Route('/deleteOffer/{id}', name: 'deleteOffer')]
    public function deleteOffer($id, OffersRepository $offerRepository, ManagerRegistry $managerRegistry): Response
    {
        $em = $managerRegistry->getManager();
        $dataid = $offerRepository->find($id);
        $em->remove($dataid);
        $em->flush();
        $this->addFlash('success', 'Offer has been deleted successfully.');
        return $this->redirectToRoute('show_offers', ['id' => $dataid->getUser()->getId()]); //done deletin redirection to the list
    }



    #[Route('/showAlloffers/{iduser}', name: 'showAlloffers')]  //show all offers
    public function showAllffers($iduser, UserRepository $userRepository, OffersRepository $offersRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $user = $userRepository->find($iduser);
        $offers = $offersRepository->findAll();
        $pagination = $paginator->paginate(
            $offers, // Query results
            $request->query->getInt('page', 1), // Current page number, default to 1
            3 // Items per page
        );
        return $this->render('user/off/OffersListing.html.twig', [
            // 'offers' => $offers, //tableau d'offres
            'pagination' => $pagination,
            'userid' => $user,

        ]);
    }

    #[Route('/showAllofferscompany/{iduser}', name: 'showAllofferscompany')]  //show all offers
    public function showAllfferscompany($iduser, UserRepository $userRepository, OffersRepository $offersRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $user = $userRepository->find($iduser);
        $offers = $offersRepository->findAll();
        $pagination = $paginator->paginate(
            $offers, // Query results
            $request->query->getInt('page', 1), // Current page number, default to 1
            3 // Items per page
        );
        return $this->render('user/off/showallofferscompany.html.twig', [
            // 'offers' => $offers, //tableau d'offres
            'pagination' => $pagination,
            'userid' => $user,
        ]);
    }

    //admin crud

    #[Route('/showoffadmin', name: 'showoffadmin', methods: ['GET'])]
    public function showoffadmin(OffersRepository $offersRepository): Response
    {
        $offers = $offersRepository->findAll();
        return $this->render('admin/showoffadmin.html.twig', [
            'offers' => $offers,

        ]);
    }

    #[Route('/deleteOfferadmin/{id}', name: 'deleteOfferadmin')]
    public function deleteOfferadmin($id, OffersRepository $offerRepository, ManagerRegistry $managerRegistry): Response
    {
        $em = $managerRegistry->getManager();
        $dataid = $offerRepository->find($id);
        $em->remove($dataid);
        $em->flush();
        return $this->redirectToRoute('showoffadmin', ['id' => $dataid->getUser()->getId()]);
    }




    //search 

    // src/Controller/OffController.php
    /*
    #[Route('/search_offers', name: 'search_offers')]
    public function searchOffers(Request $request, OffersRepository $offersRepository): Response
    {
        // Retrieve criteria from the request query parameters
        $entrepriseName = $request->query->get('entrepriseName');
        $domain = $request->query->get('domain');
        $post = $request->query->get('post');

        // Create an array to hold criteria
        $criteria = [];

        // Add criteria to the array if they are not empty
        if ($entrepriseName) {
            $criteria['entrepriseName'] = $entrepriseName;
        }
        if ($domain) {
            $criteria['domain'] = $domain;
        }
        if ($post) {
            $criteria['post'] = $post;
        }

        // Filter offers based on the given criteria
        $results = $offersRepository->findByCriteria($criteria);

        // Serialize the results to JSON and return as JsonResponse
        return new JsonResponse($results);
    }*/
}
