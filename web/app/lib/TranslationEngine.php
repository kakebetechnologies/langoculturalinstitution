<?php
declare(strict_types=1);

/**
 * Dictionary + grammar based English <-> Lango translator.
 *
 * There is no large parallel sentence corpus for Lango, only a headword
 * dictionary (with multi-word phrases and reduplicated ideophones), a set
 * of grammar notes, and the ~700 worked example sentences embedded in
 * those grammar notes. So translation here is layered lookup:
 *
 *   1. Exact match against the attested example-sentence table (real
 *      textbook translations, so these are trustworthy whole).
 *   2. Greedy longest-phrase-match against the word/phrase dictionary.
 *   3. Word-by-word fallback, with untouched pass-through for anything
 *      not found anywhere.
 */
final class TranslationEngine
{
    private const MAX_PHRASE_WORDS = 7;

    /** @var array<string, array{headword?:string, translations?:string[], english_meanings?:string[]}> */
    private array $enToLango;

    /** @var array<string, array{lango?:string, english_meanings?:string[]}> */
    private array $langoToEn;

    /** @var array<string, array{lango:string}> keyed by normalized English sentence/phrase */
    private array $sentenceEnToLango;

    /** @var array<string, array{english:string}> keyed by normalized Lango sentence/phrase */
    private array $sentenceLangoToEn;

    public function __construct(
        array $enToLango,
        array $langoToEn,
        array $sentenceEnToLango = [],
        array $sentenceLangoToEn = []
    ) {
        $this->enToLango = $enToLango;
        $this->langoToEn = $langoToEn;
        $this->sentenceEnToLango = $sentenceEnToLango;
        $this->sentenceLangoToEn = $sentenceLangoToEn;
    }

    public static function fromDataFiles(
        string $enToLangoPath,
        string $langoToEnPath,
        ?string $sentencePhrasesPath = null
    ): self {
        $sentenceEnToLango = [];
        $sentenceLangoToEn = [];

        if ($sentencePhrasesPath !== null && is_file($sentencePhrasesPath)) {
            $phrases = self::loadJson($sentencePhrasesPath);
            $sentenceEnToLango = $phrases['en_to_lango'] ?? [];
            $sentenceLangoToEn = $phrases['lango_to_en'] ?? [];
        }

        return new self(
            self::loadJson($enToLangoPath),
            self::loadJson($langoToEnPath),
            $sentenceEnToLango,
            $sentenceLangoToEn
        );
    }

    private static function loadJson(string $path): array
    {
        $raw = file_get_contents($path);
        if ($raw === false) {
            throw new RuntimeException("Unable to read dictionary file: $path");
        }
        $data = json_decode($raw, true);
        if (!is_array($data)) {
            throw new RuntimeException("Invalid JSON in dictionary file: $path");
        }
        return $data;
    }

    /**
     * @return array{translation:string, unknown_words:string[]}
     */
    public function translate(string $text, string $direction): array
    {
        $sentenceMatch = $this->matchSentence($text, $direction);
        if ($sentenceMatch !== null) {
            return [
                'translation' => $sentenceMatch,
                'unknown_words' => [],
            ];
        }

        $dictionary = $direction === 'lango-en' ? $this->langoToEn : $this->enToLango;
        $translationsField = $direction === 'lango-en' ? 'english_meanings' : 'translations';
        $sentenceMap = $direction === 'lango-en' ? $this->sentenceLangoToEn : $this->sentenceEnToLango;
        $sentenceField = $direction === 'lango-en' ? 'english' : 'lango';

        // Alternating [separator, word, separator, word, ..., separator]
        $parts = preg_split(
            '/([\p{L}\p{M}]+(?:[\'\-][\p{L}\p{M}]+)*)/u',
            $text,
            -1,
            PREG_SPLIT_DELIM_CAPTURE
        );
        if ($parts === false) {
            $parts = [$text];
        }

        $wordIndices = [];
        foreach ($parts as $idx => $part) {
            if ($idx % 2 === 1) {
                $wordIndices[] = $idx;
            }
        }

        $unknown = [];
        $wPos = 0;
        $wCount = count($wordIndices);

        while ($wPos < $wCount) {
            $match = $this->longestMatch(
                $parts,
                $wordIndices,
                $wPos,
                $wCount,
                $dictionary,
                $translationsField,
                $sentenceMap,
                $sentenceField
            );

            if ($match === null && $direction === 'lango-en') {
                $decoded = $this->decodeSubjectPrefix(
                    $parts[$wordIndices[$wPos]],
                    $dictionary,
                    $translationsField,
                    $sentenceMap,
                    $sentenceField
                );
                if ($decoded !== null) {
                    $match = [$decoded, 1];
                }
            }

            if ($match !== null) {
                [$translated, $wordsConsumed] = $match;
                $firstWordIdx = $wordIndices[$wPos];
                $parts[$firstWordIdx] = $this->matchCase($parts[$firstWordIdx], $translated);

                // Collapse the remaining consumed words (and the separators
                // between them) since their meaning is now in $translated.
                $lastWordIdx = $wordIndices[$wPos + $wordsConsumed - 1];
                for ($p = $firstWordIdx + 1; $p <= $lastWordIdx; $p++) {
                    $parts[$p] = '';
                }
                $wPos += $wordsConsumed;
            } else {
                $idx = $wordIndices[$wPos];
                $unknown[] = $parts[$idx];
                $wPos++;
            }
        }

        return [
            'translation' => implode('', $parts),
            'unknown_words' => array_values(array_unique($unknown)),
        ];
    }

    /**
     * Greedy longest match starting at word position $wPos. Only merges
     * across whitespace-only separators, never across punctuation, so a
     * phrase can't be assembled from two different clauses.
     *
     * @param string[] $parts
     * @param int[] $wordIndices
     * @return array{0:string,1:int}|null [translation, wordsConsumed]
     */
    private function longestMatch(
        array $parts,
        array $wordIndices,
        int $wPos,
        int $wCount,
        array $dictionary,
        string $translationsField,
        array $sentenceMap,
        string $sentenceField
    ): ?array {
        $span = min(self::MAX_PHRASE_WORDS, $wCount - $wPos);

        // Try the longest possible phrase first, shrinking down to a single
        // word, so a multi-word dictionary entry always wins over a partial
        // match of just its first word.
        for ($len = $span; $len >= 1; $len--) {
            $words = [];
            for ($k = 0; $k < $len; $k++) {
                if ($k > 0) {
                    $gapIdx = $wordIndices[$wPos + $k - 1] + 1;
                    if (trim($parts[$gapIdx]) !== '') {
                        continue 2;
                    }
                }
                $words[] = $parts[$wordIndices[$wPos + $k]];
            }
            $key = mb_strtolower(implode(' ', $words), 'UTF-8');
            $translated = $this->lookupTranslation($key, $dictionary, $translationsField, $sentenceMap, $sentenceField);
            if ($translated !== null) {
                return [$translated, $len];
            }
        }

        return null;
    }

    /**
     * Looks up a word/phrase key in the primary dictionary first, falling
     * back to the single-word and short-phrase entries embedded in the
     * grammar's worked examples (e.g. "kwene?" -> "where?"), which never
     * made it into the headword dictionary itself.
     */
    private function lookupTranslation(
        string $key,
        array $dictionary,
        string $dictField,
        array $sentenceMap,
        string $sentenceField
    ): ?string {
        $options = $dictionary[$key][$dictField] ?? [];
        if (!empty($options)) {
            return $options[0];
        }

        $sentenceValue = $sentenceMap[$key][$sentenceField] ?? null;
        if ($sentenceValue !== null) {
            $cleaned = rtrim(trim($sentenceValue), ".,!?;: ");
            if ($cleaned !== '') {
                return $cleaned;
            }
        }

        return null;
    }

    /**
     * Lango marks the subject as a prefix fused onto the verb rather than
     * a separate word (a- = I, i- = you, u- = you pl.; see grammar rule
     * "pronoun_personal" / "verb_present_past_future_tense"). The dictionary
     * only has bare verb stems, so "iwoto" ("you go") never matches "woto"
     * ("go") directly. Only the unambiguous prefixes are decoded here —
     * "o-"/"ε-" also mark he/we/they depending on context, and guessing
     * among those would be more likely to mislead than to help.
     */
    private function decodeSubjectPrefix(
        string $word,
        array $dictionary,
        string $dictField,
        array $sentenceMap,
        string $sentenceField
    ): ?string {
        static $prefixes = [
            'a' => 'I',
            'i' => 'you',
            'u' => 'you (pl.)',
        ];

        $lower = mb_strtolower($word, 'UTF-8');
        $prefix = mb_substr($lower, 0, 1, 'UTF-8');
        if (!isset($prefixes[$prefix])) {
            return null;
        }

        $stem = mb_substr($lower, 1, null, 'UTF-8');
        if (mb_strlen($stem, 'UTF-8') < 2) {
            return null;
        }

        $verbMeaning = $this->lookupTranslation($stem, $dictionary, $dictField, $sentenceMap, $sentenceField);
        if ($verbMeaning === null) {
            return null;
        }

        return $prefixes[$prefix] . ' ' . $verbMeaning;
    }

    /**
     * Whole-sentence/phrase exact match against the attested example
     * table, so known textbook sentences translate verbatim instead of
     * being pulled apart word by word.
     */
    private function matchSentence(string $text, string $direction): ?string
    {
        $key = $this->normalizeSentenceKey($text);
        if ($key === '') {
            return null;
        }

        if ($direction === 'lango-en') {
            return $this->sentenceLangoToEn[$key]['english'] ?? null;
        }
        return $this->sentenceEnToLango[$key]['lango'] ?? null;
    }

    private function normalizeSentenceKey(string $text): string
    {
        $text = trim($text);
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;
        $text = rtrim($text, ".,!?;: ");
        return mb_strtolower($text, 'UTF-8');
    }

    private function matchCase(string $originalWord, string $translated): string
    {
        if ($originalWord === '' || $translated === '') {
            return $translated;
        }
        $firstChar = mb_substr($originalWord, 0, 1, 'UTF-8');
        if ($firstChar === mb_strtoupper($firstChar, 'UTF-8') && $firstChar !== mb_strtolower($firstChar, 'UTF-8')) {
            return mb_strtoupper(mb_substr($translated, 0, 1, 'UTF-8'), 'UTF-8')
                . mb_substr($translated, 1, null, 'UTF-8');
        }
        return $translated;
    }
}
