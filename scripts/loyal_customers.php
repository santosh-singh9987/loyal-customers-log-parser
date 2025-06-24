<?php
// CLI usage only
if (php_sapi_name() !== 'cli') {
    exit("This script must be run from the command line.\n");
}

/**
 * Usage:
 * php loyal_customers.php logs/log_day1.txt logs/log_day2.txt
 */
 
if ($argc !== 3) {
    exit("Usage: php loyal_customers.php <log_day1_file> <log_day2_file>\n");
}

$logFileDay1 = $argv[1];
$logFileDay2 = $argv[2];

// Function to parse a log file and return customer => unique page set
function parseLogFile($filePath)
{
    if (!file_exists($filePath)) {
        exit("File not found: $filePath\n");
    }

    $customerVisits = [];

    $file = fopen($filePath, 'r');
    if (!$file) {
        exit("Error opening file: $filePath\n");
    }

    while (($line = fgets($file)) !== false) {

        [$timestamp, $pageId, $customerId] = array_map('trim', preg_split('/\s+/', $line));

        // Initialize if customer not seen before
        if (!isset($customerVisits[$customerId])) {
            $customerVisits[$customerId] = [];
        }

        // Add unique page IDs
        $customerVisits[$customerId][$pageId] = true;
    }
    fclose($file);

    // Convert inner arrays to page count
    foreach ($customerVisits as $customerId => $pages) {
        $customerVisits[$customerId] = count($pages);
    }

    return $customerVisits;
}

// Parse both logs
$day1Data = parseLogFile($logFileDay1);
$day2Data = parseLogFile($logFileDay2);

// Identify loyal customers
$loyalCustomers = [];

foreach ($day1Data as $customerId => $day1Pages) {
    if (isset($day2Data[$customerId]) && $day1Pages >= 2 && $day2Data[$customerId] >= 2) {
        $loyalCustomers[] = $customerId;
    }
}
// Output result
echo "Loyal Customers (visited both days and at least 2 unique pages each day):\n";
foreach ($loyalCustomers as $customerId) {
    echo $customerId . PHP_EOL;
}