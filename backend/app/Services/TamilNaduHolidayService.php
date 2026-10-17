<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class TamilNaduHolidayService
{
    /**
     * Fetch Tamil Nadu / India public holidays for a year (external + config fallback).
     *
     * @return array<int, array{date: string, name: string, source: string}>
     */
    public function fetchForYear(int $year): array
    {
        $merged = [];
        $seen = [];

        foreach ($this->fetchFromNagerDate($year) as $h) {
            $key = $h['date'];
            if (!isset($seen[$key])) {
                $merged[] = $h;
                $seen[$key] = true;
            }
        }

        foreach ($this->fetchFromOpenHolidayApi($year) as $h) {
            $key = $h['date'];
            if (!isset($seen[$key])) {
                $merged[] = $h;
                $seen[$key] = true;
            }
        }

        foreach ($this->fetchFromConfig($year) as $h) {
            $key = $h['date'];
            if (!isset($seen[$key])) {
                $merged[] = $h;
                $seen[$key] = true;
            }
        }

        usort($merged, function ($a, $b) {
            return strcmp($a['date'], $b['date']);
        });

        return $merged;
    }

    /**
     * Holidays in a date range (for collection prediction).
     */
    public function holidaysBetween(Carbon $from, Carbon $to): array
    {
        $years = range((int) $from->format('Y'), (int) $to->format('Y'));
        $all = [];
        $seen = [];

        foreach ($years as $year) {
            foreach ($this->fetchForYear($year) as $h) {
                if ($h['date'] >= $from->toDateString() && $h['date'] <= $to->toDateString()) {
                    if (!isset($seen[$h['date'] . '|' . $h['name']])) {
                        $all[] = $h;
                        $seen[$h['date'] . '|' . $h['name']] = true;
                    }
                }
            }
        }

        return $all;
    }

    protected function fetchFromNagerDate(int $year): array
    {
        try {
            $response = Http::timeout(8)->get("https://date.nager.at/api/v3/PublicHolidays/{$year}/IN");
            if (!$response->successful()) {
                return [];
            }

            $out = [];
            foreach ($response->json() as $row) {
                if (empty($row['date']) || empty($row['localName'])) {
                    continue;
                }
                $out[] = [
                    'date' => Carbon::parse($row['date'])->toDateString(),
                    'name' => $row['localName'],
                    'source' => 'Nager.Date (India national)',
                ];
            }

            return $out;
        } catch (\Exception $e) {
            return [];
        }
    }

    protected function fetchFromOpenHolidayApi(int $year): array
    {
        try {
            $from = $year . '-01-01';
            $to = $year . '-12-31';
            $response = Http::timeout(8)->get('https://openholidaysapi.org/PublicHolidays', [
                'countryIsoCode' => 'IN',
                'subdivisionCode' => 'IN-TN',
                'validFrom' => $from,
                'validTo' => $to,
            ]);

            if (!$response->successful()) {
                return [];
            }

            $out = [];
            foreach ($response->json() as $row) {
                $start = $row['startDate'] ?? ($row['date'] ?? null);
                $name = $row['name'][0]['text'] ?? ($row['name'] ?? null);
                if (!$start || !$name) {
                    continue;
                }
                $out[] = [
                    'date' => Carbon::parse($start)->toDateString(),
                    'name' => is_array($name) ? ($name['text'] ?? 'Holiday') : $name,
                    'source' => 'OpenHolidays API (Tamil Nadu)',
                ];
            }

            return $out;
        } catch (\Exception $e) {
            return [];
        }
    }

    protected function fetchFromConfig(int $year): array
    {
        $config = config('tamil_nadu_holidays', []);
        $rows = isset($config[$year]) ? $config[$year] : [];

        $out = [];
        foreach ($rows as $row) {
            $out[] = [
                'date' => $row['date'],
                'name' => $row['name'],
                'source' => 'Tamil Nadu config fallback',
            ];
        }

        return $out;
    }
}
