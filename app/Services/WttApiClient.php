<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class WttApiClient
{
    private const BASE_URL = 'https://liveeventsapi.worldtabletennis.com/api/cms/GetBrackets';

    private const SUB_EVENTS = [
        'TTEMSINGLES' => "Men's Singles",
        'TTEWSINGLES' => "Women's Singles",
    ];

    private array $headers;

    public function __construct()
    {
        $this->headers = [
            'Origin' => 'https://www.worldtabletennis.com',
            'Referer' => 'https://www.worldtabletennis.com/',
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        ];
    }

    public function fetchBracket(int $eventId, string $subEventCode): array
    {
        $url = self::BASE_URL.'/'.$eventId.'/'.$subEventCode;

        $response = Http::withHeaders($this->headers)
            ->timeout(30)
            ->get($url);

        if ($response->failed()) {
            throw new RuntimeException(
                "WTT API request failed for {$subEventCode}: {$response->status()}"
            );
        }

        return $response->json();
    }

    public function fetchAllMatches(int $eventId): array
    {
        $allMatches = [];
        $competition = null;

        foreach (self::SUB_EVENTS as $code => $name) {
            try {
                $bracketData = $this->fetchBracket($eventId, $code);

                if ($competition === null) {
                    $competition = $bracketData['Competition'] ?? [];
                }

                $matches = $this->extractMatches($bracketData, $name);
                $allMatches = array_merge($allMatches, $matches);
            } catch (\Exception $e) {
                logger()->warning("WTT sync: failed to fetch {$code} for event {$eventId}: {$e->getMessage()}");
            }
        }

        return [
            'matches' => $allMatches,
            'competition' => $competition ?? [],
        ];
    }

    private function extractMatches(array $bracketData, string $subEventName): array
    {
        $matches = [];
        $brackets = $bracketData['Competition']['Bracket'] ?? [];

        foreach ($brackets as $b) {
            $bracketCode = $b['Code'] ?? '';

            if ($bracketCode !== 'MAIN') {
                continue;
            }

            $items = $b['BracketItems'] ?? [];

            foreach ($items as $item) {
                $ci = $item['BracketItem'] ?? [];

                foreach ($ci as $match) {
                    $code = trim($match['Code'] ?? '');

                    if ($code === '') {
                        continue;
                    }

                    $competitors = $match['CompetitorPlace'] ?? [];
                    $playerAIttfId = null;
                    $playerBIttfId = null;
                    $winnerIttfId = null;
                    $playerASets = 0;
                    $playerBSets = 0;

                    if (count($competitors) >= 2) {
                        $c1 = $competitors[0];
                        $c2 = $competitors[1];
                        $comp1 = $c1['Competitor'] ?? [];
                        $comp2 = $c2['Competitor'] ?? [];

                        if ($comp1) {
                            $playerAIttfId = (string) ($comp1['Code'] ?? '');
                        }

                        if ($comp2) {
                            $playerBIttfId = (string) ($comp2['Code'] ?? '');
                        }

                        $playerASets = (int) ($c1['Result'] ?? 0);
                        $playerBSets = (int) ($c2['Result'] ?? 0);

                        if (($c1['Wlt'] ?? '') === 'W' && $playerAIttfId) {
                            $winnerIttfId = $playerAIttfId;
                        } elseif (($c2['Wlt'] ?? '') === 'W' && $playerBIttfId) {
                            $winnerIttfId = $playerBIttfId;
                        }
                    }

                    $resultStr = $match['Result'] ?? '';
                    [$overallScores, $gameScores] = $this->parseScoreString($resultStr);

                    if ($overallScores === '' && $playerASets > 0 && $playerBSets > 0) {
                        $overallScores = "{$playerASets}-{$playerBSets}";
                    }

                    $matches[] = [
                        'document_code' => $code,
                        'sub_event' => $subEventName,
                        'player_a_ittf_id' => $playerAIttfId,
                        'player_b_ittf_id' => $playerBIttfId,
                        'overall_scores' => $overallScores,
                        'game_scores' => $gameScores,
                        'winner_ittf_id' => $winnerIttfId,
                        'date' => $match['Date'] ?? '',
                        'completed' => (bool) $resultStr,
                        'player_a_sets' => $playerASets,
                        'player_b_sets' => $playerBSets,
                    ];
                }
            }
        }

        return $matches;
    }

    private function parseScoreString(string $resultStr): array
    {
        if ($resultStr === '') {
            return ['', ''];
        }

        if (preg_match('/^(\d+-\d+)\s*\(([^)]+)\)$/', $resultStr, $m)) {
            $overall = $m[1];
            $gamesRaw = $m[2];
            $games = array_filter(
                array_map('trim', explode(',', $gamesRaw)),
                fn (string $g): bool => $g !== '' && $g !== '0:0',
            );

            return [$overall, implode(',', $games)];
        }

        if (preg_match('/^(\d+-\d+)/', $resultStr, $m)) {
            return [$m[1], ''];
        }

        return ['', ''];
    }
}
