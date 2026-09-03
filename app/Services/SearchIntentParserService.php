<?php

namespace App\Services;

class SearchIntentParserService
{
    /**
     * Category mapping dictionary for common natural language search terms.
     *
     * Every value is one of the five category names, so what a customer types
     * resolves to the same word the database, the URLs and the theme matrix
     * use.
     */
    protected array $categoryMap = [
        'haircut'     => 'beauty',
        'shave'       => 'beauty',
        'beard'       => 'beauty',
        'salon'       => 'beauty',
        'facial'      => 'beauty',
        'spa'         => 'beauty',
        'makeup'      => 'beauty',
        'doctor'      => 'health',
        'clinic'      => 'health',
        'dentist'     => 'health',
        'dental'      => 'health',
        'checkup'     => 'health',
        'consultant'  => 'consultant',
        'advisory'    => 'consultant',
        'trainer'     => 'education',
        'coaching'    => 'education',
        'tuition'     => 'education',
        'gym'         => 'sports',
        'badminton'   => 'sports',
        'turf'        => 'sports',
    ];

    /**
     * Parse natural language input into structured intent parameters.
     */
    public function parse(string $rawInput): array
    {
        $input = strtolower(trim($rawInput));
        if (empty($input)) {
            return [
                'raw_query'        => '',
                'clean_keyword'    => '',
                'inferred_category' => null,
                'time_intent'      => null,
            ];
        }

        $tokens = preg_split('/\s+/', $input);
        $inferredCategory = null;
        $timeIntent = null;
        $filteredTokens = [];

        foreach ($tokens as $token) {
            // Match time intent
            if (in_array($token, ['morning', 'am'])) {
                $timeIntent = 'morning';
                continue;
            } elseif (in_array($token, ['afternoon', 'noon', 'pm'])) {
                $timeIntent = 'afternoon';
                continue;
            } elseif (in_array($token, ['evening', 'night'])) {
                $timeIntent = 'evening';
                continue;
            } elseif (in_array($token, ['now', 'open', 'urgent', 'emergency'])) {
                $timeIntent = 'open_now';
                continue;
            }

            // Match category intent
            if (!$inferredCategory && isset($this->categoryMap[$token])) {
                $inferredCategory = $this->categoryMap[$token];
            }

            // Skip filler words
            if (in_array($token, ['near', 'me', 'in', 'at', 'for', 'a', 'the', 'best', 'top', 'open', 'tomorrow', 'today'])) {
                continue;
            }

            $filteredTokens[] = $token;
        }

        return [
            'raw_query'         => $rawInput,
            'clean_keyword'     => implode(' ', $filteredTokens),
            'inferred_category' => $inferredCategory,
            'time_intent'       => $timeIntent,
        ];
    }
}
