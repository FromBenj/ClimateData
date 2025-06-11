<?php

namespace App\Controller;

use App\Service\DataService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

final class TemperatureController extends AbstractController
{


    #[Route('/temperature', name: 'app_temperature')]
    public function temperatureAnalysis(DataService $dataService, ChartBuilderInterface $chartBuilder): Response
    {
        $countries = ['Americas', 'Africa', 'Europe', 'Asia', 'Oceania'];
        $data = $dataService->getTemperatureData('/assets/data/temperature_changes.csv', $countries);
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
        };
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
            'temper_chart' => $temperChart,
        ]);
    }
}
