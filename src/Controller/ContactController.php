<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Form\ContactType;
use App\Repository\ContactRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController
{
    #[Route('/', name:'contact')]
    public function index(ContactRepository $cr)
    {
        $liste_contact = $cr->findAll();
        return $this->render('contact/index.html.twig', [
            'contacts' => $liste_contact
        ]);
    }

    #[Route('/contact/show/{id}', name:'contact_info')]
    public function information($id, ContactRepository $cr):Response
    {
        $contact = $cr->find($id);
        return $this->render('contact/info.html.twig', [
            'contact' => $contact  
        ]);
    }

    #[Route('/contact/delete/{id}', name: 'contact_delete')]
    public function delete(ManagerRegistry $doctrine, $id)
    {
        $contact = $doctrine->getRepository(Contact::class)->find($id);
        $entityManager = $doctrine->getManager();
        $entityManager->remove($contact);
        $entityManager->flush();

        $this->addFlash('danger', 'Contact supprimé avec succès');
        return $this->redirectToRoute('contact');

    }

    #[Route('/contact/add', name: 'contact_add')]
    public function add(Request $request, EntityManagerInterface $em):Response
    {
       // On crée une nouvelle annonce
        $contact = new Contact();
        // On crée le formulaire
        $contactForm = $this->createForm(ContactType::class, $contact);

        // On traite la requête du formulaire
        $contactForm->handleRequest($request);

        //On vérifie si le formulaire est soumis ET valide
        if($contactForm->isSubmitted() && $contactForm->isValid())
        {
            $em->persist($contact);
            $em->flush();

            $this->addFlash('success', 'Contact ajoutée avec succès');
            return $this->redirectToRoute('contact');
        }
        
        return $this->render('contact/add.html.twig',[
           'contact' => $contactForm->createView()
        ]);
    }

    #[Route('/contact/edit/{id}', name: 'contact_edit')]
    public function edit(ManagerRegistry $doctrine, Request $request, $id)
    { 

        $em = $doctrine->getManager();

        $contact = $doctrine->getRepository(Contact::class)->find($id);
        if(!$contact)
        {
            $contact = new Contact();
        }
               
         // On crée le formulaire
         $contactForm = $this->createForm(ContactType::class, $contact);

         // On traite la requête du formulaire
         $contactForm->handleRequest($request);

         //On vérifie si le formulaire est soumis ET valide
         if($contactForm->isSubmitted() && $contactForm->isValid())
         {
            if(!$contact->getId()){
                $em->persist($contact);
            }
             $em->flush();

             $this->addFlash('success', 'Contact modifiée avec succès');
             return $this->redirectToRoute('contact');
         }
         return $this->render('contact/edit.html.twig',[
            'contact' => $contactForm->createView()
         ]);
    }
      
}   
