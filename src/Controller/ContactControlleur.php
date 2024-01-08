<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Form\ContactType;
use Symfony\Component\Mime\Email;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Notifier\Exception\TransportExceptionInterface;

class ContactControlleur extends AbstractController
{
    #[Route('/contact', name: 'contact.index', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        EntityManagerInterface $manager,
        MailerInterface $mailer
    ): Response {
        $contact = new Contact();
        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Envoie de l'email
                $email = (new Email())
                    ->from($contact->getEmail())
                    ->to('aouniibrahim94@gmail.com')
                    ->subject($contact->getSubject())
                    ->html($contact->getMessage());
        
                $mailer->send($email);
        
                // Sauvegarde des données du formulaire en base de données
                $manager->persist($contact);
                $manager->flush();
        
                // Redirection après soumission réussie
                return $this->redirectToRoute('contact.index');
            } catch (TransportExceptionInterface $e) {
                // Erreur lors de l'envoi d'email
                $this->addFlash('error', 'Une erreur s\'est produite lors de l\'envoi de l\'email.');
        
              
            }
        }
        return $this->render('pages/contact/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}

