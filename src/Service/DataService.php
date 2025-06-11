<?php

namespace App\Service;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DataService extends AbstractController
{
    const TEMPER_FIRST_YEAR = "1961";
    const TEMPER_LAST_YEAR = "2024";
    const BASE = 0;

    function getTemperatureData(string $path, array|string $countries): array
    {
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
            if ($countries === "all" || in_array($row[$countryColumn], $countries, true)) {
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
                    'average' => array_sum(array_map('floatval', $yearsData)) / count($yearsData),
                ];
            }
        }
        $data['labels'] = $labelsData;
        $data['base'] = $base;

        return $data;
    }
}
