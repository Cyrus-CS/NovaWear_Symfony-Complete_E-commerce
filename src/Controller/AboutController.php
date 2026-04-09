<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

class AboutController extends AbstractController
{
    #[Route('/about', name: 'about', methods: ['GET'])]
    public function index()
    {
        return $this->render('primary_menu/about.html.twig', [
            'controller_name' => 'AboutController',
        ]);
    }
}