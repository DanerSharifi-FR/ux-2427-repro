<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ReproController extends AbstractController
{
    private const FOODS = [
        'cookies' => 'Cookies',
        'cooking' => 'Cooking',
        'coconut' => 'Coconut',
        'tea' => 'Tea',
        'apple' => 'Apple',
    ];

    #[Route('/', name: 'app_repro')]
    public function index(): Response
    {
        $form = $this->createFormBuilder()
            ->add('foods', ChoiceType::class, [
                'label' => 'Foods',
                'choices' => [],
                'multiple' => true,
                'autocomplete' => true,
                'autocomplete_url' => $this->generateUrl('app_foods_autocomplete'),
            ])
            ->getForm();

        return $this->render('repro/index.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/foods-autocomplete', name: 'app_foods_autocomplete')]
    public function autocomplete(Request $request): JsonResponse
    {
        $query = strtolower($request->query->getString('query'));

        $results = [];

        foreach (self::FOODS as $value => $label) {
            if ('' !== $query && !str_contains(strtolower($label), $query)) {
                continue;
            }

            $results[] = [
                'value' => $value,
                'text' => $label,
            ];
        }

        return $this->json([
            'results' => $results,
            'next_page' => null,
        ]);
    }
}
