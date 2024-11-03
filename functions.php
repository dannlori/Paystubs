<?php
// Function to extract a value using a regular expression
function extractValue($text, $pattern)
{
    if (preg_match($pattern, $text, $match)) {
        // Remove commas from the matched value
        $number = str_replace(',', '', $match[1]);
        return floatval($number);
    } else {
        return 0.0; // Set default value if not found
    }
}
// Function to extract all matches for a regex pattern
function extractAllValues($text, $pattern)
{
    preg_match_all($pattern, $text, $matches);
    return isset($matches[1]) ? $matches[1] : [];
}

// Function to sum values in an array
function sumValues($values)
{
    return array_sum(array_map('floatval', $values));
}
