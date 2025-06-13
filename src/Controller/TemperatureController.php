<?php

namespace App\Controller;

use App\Service\DataService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

#[Route('/temperature', name: 'temperature_')]
final class TemperatureController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(): RedirectResponse
    {
        return $this->redirectToRoute('temperature_evolution');
    }

    #[Route('/evolution', name: 'evolution')]
    public function temperatureAnalysis(DataService $dataService, ChartBuilderInterface $chartBuilder): Response
    {
        $countries = ['World', 'Americas', 'Africa', 'Europe', 'Asia', 'Oceania'];
        $data = $dataService->getCountriesTemperature($countries);
        $temperChart = $chartBuilder->createChart(Chart::TYPE_LINE);
        $datasets = [];
        foreach ($data['data'] as $row) {
            $color = 'rgb(' . random_int(0, 240) . ', ' . random_int(0, 240) . ', ' . random_int(0, 240) . ')';
            $datasets[] = [
                'label' => $row['country'],
                'backgroundColor' => $color,
                'borderColor' => $color,
                'data' => $row['values'],
                'tension' => 0.4,
            ];
        }
        $baseDataset = [
            'borderColor' => 'rgb(192, 192, 192)',
            'data' => $data['base'],
            'label' => "Base",
            'legend' => [
                'display' => false,
            ],
            'elements' => [
                'point' => [
                    'radius' => 0,
                ],
            ],
        ];
        $datasets[] = $baseDataset;
        $temperChart->setData([
            'labels' => $data['labels'],
            'datasets' => $datasets,
        ]);

        $temperChart->setOptions([
            'responsive' => true,
            'scales' => [
                'y' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Temperature changes (C°)'
                    ],
                    'suggestedMin' => -4,
                    'suggestedMax' => 4,
                ],
            ],
        ]);

        return $this->render('temperature/index.html.twig', [
            'title' => 'Temperature Evolution (1961-2024)',
            'temper_chart' => $temperChart,
        ]);
    }

    #[Route('/average', name: 'average')]
    public function averageEvolution(DataService $dataService, ChartBuilderInterface $chartBuilder): Response
    {
        $countries = ['World','Americas', 'Africa', 'Europe', 'Asia', 'Oceania'];
        $data = $dataService->getCountriesAverageEvolution($countries);
        $countryLabels = [];
        $averageTemperData = [];
        $color = [];
        foreach ($data['data'] as $row) {
            $countryLabels[] = $row['country'];
            $averageTemperData[] = $row['average'];
            $color[] = 'rgb(' . random_int(0, 240) . ', ' . random_int(0, 240) . ', ' . random_int(0, 240) . ')';
        }

        $averageTemperChart = $chartBuilder->createChart(Chart::TYPE_BAR);
        $averageTemperChart->setData([
            'labels' => $countryLabels,
            'datasets' => [
                [
                    'label' => "",
                    'data' => $averageTemperData,
                    'backgroundColor' => $color,
                ],
            ]]);

        $averageTemperChart->setOptions([
            'responsive' => true,
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Temperature (C°)'
                    ],
                ]
            ]
        ]);

        return $this->render('temperature/index.html.twig', [
            'title'                   => 'Average Temperature Evolution Per Area (1961-2024)',
            'temper_chart' => $averageTemperChart,
        ]);
    }
}
