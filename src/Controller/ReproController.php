<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ReproController extends AbstractController
{
    #[Route('/', name: 'app_repro')]
    public function index(): Response
    {
        $form = $this->createFormBuilder()
            ->add('foods', ChoiceType::class, [
                'label' => 'Foods',
                'choices' => [
                    'Cookies' => 'cookies',
                    'Cooking' => 'cooking',
                    'Coconut' => 'coconut',
                    'Tea' => 'tea',
                    'Apple' => 'apple',
                ],
                'multiple' => true,
                'autocomplete' => true,
            ])
            ->getForm();

        return $this->render('repro/index.html.twig', [
            'form' => $form,
        ]);
    }
}
