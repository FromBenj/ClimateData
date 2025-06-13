<?php

namespace App\Service;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DataService extends AbstractController
{

    const PATH = '/assets/data/temperature_changes.csv';
    const TEMPER_FIRST_YEAR = "1961";
    const TEMPER_LAST_YEAR = "2024";
    const BASE = 0;

    function getTemperatureData(): array
    {
        $path = self::PATH;
        $rawData = [];
        $csvFileName = $this->getParameter('kernel.project_dir') . $path;
        $csvFile = fopen($csvFileName, 'rb');
        while (($row = fgetcsv($csvFile)) !== false) {
            $rawData[] = $row;
        }
        fclose($csvFile);
        $countryColumn = array_search('Country', $rawData[0]);
        $firstYearColumn = array_search(self::TEMPER_FIRST_YEAR, $rawData[0]);
        $lastYearColumn = array_search(self::TEMPER_LAST_YEAR, $rawData[0]);
        $rawLabels = $rawData[0];
        unset($rawData[0]);
        $data = [];
        foreach ($rawData as $row) {
            $countryData = $row[$countryColumn];
            $yearsData = [];
            $labelsData = [];
            $base = [];
            for ($i = $firstYearColumn; $i <= $lastYearColumn; $i++) {
                $yearsData[] = $row[$i];
                $labelsData[] = $rawLabels[$i];
                $base[] = self::BASE;
            }
            $data['data'][] = [
                'country' => $countryData,
                'values' => $yearsData,
            ];
        }
        $data['labels'] = $labelsData;
        $data['base'] = $base;

        return $data;
    }

    function getCountriesTemperature(array|null $countries): array
    {
        $rawData = $this->getTemperatureData();
        $allCountries = [];
        $data = [];
        foreach ($rawData['data'] as $row) {
            $allCountries[] = $row['country'];
        }
        if ($countries === null) {
            $data = $rawData;
        } elseif (empty(array_diff($countries, $allCountries))) {
            $data['data'] = array_filter($rawData['data'], function ($row) use ($countries) {
                return in_array($row['country'], $countries);
            });
        }

        $data['labels'] = $rawData['labels'];
        $data['base'] = $rawData['base'];

        return $data;
    }

    function getCountriesAverageEvolution(array|null $countries): array
    {
        $rawData = $this->getTemperatureData();
        $allCountries = [];
        $data = [];
        $dataPicking = [];
        foreach ($rawData['data'] as $row) {
            $allCountries[] = $row['country'];
        }
        if ($countries === null) {
            foreach ($rawData['data'] as $row) {
                $data['data'][] = [
                    'country' => $row['country'],
                    'average' => array_sum(array_map('floatval', $row['values'])) / count($row['values']),
                ];
            }
        } elseif (empty(array_diff($countries, $allCountries))) {
            $dataPicking['data'] = array_filter($rawData['data'], function ($row) use ($countries) {
                return in_array($row['country'], $countries);
            });
            foreach ($dataPicking['data'] as $row) {
                $data['data'][] = [
                    'country' => $row['country'],
                    'average' => array_sum(array_map('floatval', $row['values'])) / count($row['values']),
                ];
            }
        }
        $data['globalData'] = [];
        $dataPicking['data'] = array_values($dataPicking['data']);
        for ($i = 0; $i <= self::TEMPER_LAST_YEAR - self::TEMPER_FIRST_YEAR; $i++) {
            $temperSum = 0;
            for ($j = 0, $iMax = count($dataPicking['data']); $j < $iMax; $j++) {
                $temperSum += $dataPicking['data'][$j]['values'][$i];
            }
            $data['globalData']['values'][$i] = $temperSum;
        }
        $data['globalData']['labels'] = $rawData['labels'];

        return $data;
    }
}
