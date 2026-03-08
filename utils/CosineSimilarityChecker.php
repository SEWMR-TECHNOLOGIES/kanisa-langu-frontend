<?php
class CosineSimilarity {
    public static function normalizeText($text) {
        return strtolower(preg_replace('/[^a-z0-9]+/i', ' ', trim($text)));
    }

    public static function textToVector($text) {
        $words = explode(' ', self::normalizeText($text));
        $vec = [];
        foreach ($words as $word) {
            if (!empty($word)) {
                $vec[$word] = ($vec[$word] ?? 0) + 1;
            }
        }
        return $vec;
    }

    public static function compute($vec1, $vec2) {
        $dot = 0.0;
        $normA = 0.0;
        $normB = 0.0;

        foreach ($vec1 as $key => $val) {
            $dot += $val * ($vec2[$key] ?? 0);
            $normA += pow($val, 2);
        }

        foreach ($vec2 as $val) {
            $normB += pow($val, 2);
        }

        return ($normA && $normB) ? $dot / (sqrt($normA) * sqrt($normB)) : 0.0;
    }
}
?>
