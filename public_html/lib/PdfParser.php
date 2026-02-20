<?php
// lib/PdfParser.php - Simple PDF Text Extractor
// Only for "On-the-Fly" usage. Not a full-fledged PDF parser.

class PdfParser
{

    public function parseContent($content)
    {
        $text = "";

        // 1. Extract Text Streams (BT ... ET blocks or stream ... endstream)
        // This is a naive approach but works for many simple PDFs like BOE
        // Real PDFs are complex graph structures. OpenBorme relies on BOE's specific format.

        // Find all stream objects
        if (preg_match_all('/stream[\r\n]+(.*?)[\r\n]+endstream/s', $content, $matches)) {
            foreach ($matches[1] as $stream) {
                $decoded = @gzuncompress($stream);
                if ($decoded !== false) {
                    $text .= $this->extractTextFromStream($decoded);
                } else {
                    // Try without compression/different filters if needed (omitted for brevity)
                    // Some streams might be raw text
                    $text .= $this->extractTextFromStream($stream);
                }
            }
        }

        return $this->cleanText($text);
    }

    private function extractTextFromStream($stream)
    {
        $text = "";

        // Regex to capture operators in order:
        // 1. (...) Tj
        // 2. [...] TJ
        // We use a non-greedy catch for content. 
        // Note: Simple parsing. Does not handle nested parentheses perfectly if not escaped.

        if (preg_match_all('/(?:\((.*?)\)\s*Tj)|(?:\[(.*?)\]\s*TJ)/s', $stream, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                // Check which group matched
                if (!empty($match[1])) {
                    // (...) Tj Case
                    $text .= $match[1];
                } elseif (!empty($match[2])) {
                    // [...] TJ Case
                    // TJ contains an array of strings and numbers (shifts). 
                    // We only want the strings: [(Text) 20 (More)]
                    if (preg_match_all('/\((.*?)\)/', $match[2], $submatches)) {
                        foreach ($submatches[1] as $submatch) {
                            $text .= $submatch;
                        }
                    }
                }
            }
        }

        return $text;
    }

    private function cleanText($text)
    {
        // Convert strict encoding if possible (WinAnsi vs UTF-8)
        // BOE PDFs are usually WinAnsiEncoded.
        // We'll treat as ISO-8859-1 (Latin1) and convert to UTF-8

        // Unescape standard PDF escapes
        $text = str_replace(['\\(', '\\)', '\\\\'], ['(', ')', '\\'], $text);

        // Attempt encoding conversion
        if (mb_detect_encoding($text, 'UTF-8', true) === false) {
            $text = mb_convert_encoding($text, 'UTF-8', 'ISO-8859-1');
        }

        return $text;
    }
}
