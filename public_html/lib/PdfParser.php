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
        // Extract text between () or inside TJ arrays
        $text = "";

        // Simple text operator extraction
        // (Text) Tj  or  [(Te) 20 (xt)] TJ

        // 1. Remove text positioning operators to avoid noise
        // This is tricky without a full parser, but let's try capturing (...)Tj

        if (preg_match_all('/\((.*?)\)\s*Tj/', $stream, $matches)) {
            foreach ($matches[1] as $m) {
                $text .= $m;
            }
        }

        // 2. Handle TJ arrays: [(H) -2 (e) -2 (l) -2 (l) -2 (o)] TJ
        if (preg_match_all('/\[(.*?)\]\s*TJ/', $stream, $matches)) {
            foreach ($matches[1] as $m) {
                // Extract strings in parentheses inside the array
                if (preg_match_all('/\((.*?)\)/', $m, $submatches)) {
                    foreach ($submatches[1] as $sm) {
                        $text .= $sm;
                    }
                }
            }
        }

        // Handle newlines mapping (T* or Td/TD with negative Y)
        // This parser returns a continuous stream, we'll rely on regex for structure
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
