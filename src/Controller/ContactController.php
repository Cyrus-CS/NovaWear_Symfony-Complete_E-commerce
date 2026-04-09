<?php
namespace App\Controller;

use App\DTO\ContactDTO;
use App\Form\ContactType;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Attribute\Route;

class ContactController extends AbstractController{

    #[Route('/contact', name:'contact', methods:['GET', 'POST'])]
    public function contact(Request $request, MailerInterface $mailer){
        $contact  = new ContactDTO();
        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $email = (new TemplatedEmail())
            ->to('contactnovawear@gmail.com')
            ->from($contact->getEmail())
            ->context(['message' => $contact->getMessage()])
            ->subject($contact->getSubject());
            $mailer->send($email);
             $this->addFlash('success', 'Your message has been sent successfully!');
             return $this->redirectToRoute('home');
        }
        return $this->render('primary_menu/contact.html.twig');
    }
}