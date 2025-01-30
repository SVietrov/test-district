<?php

/**
 * Generates random district data with different building types.
 *
 * @param int $numDistricts Number of districts to generate.
 * @return array District data.
 */
function generateDistrictData(int $numDistricts): array
{
    $districts = [];
    $buildingTypes = ['hospital', 'school', 'restaurant', 'gym'];

    for ($i = 0; $i < $numDistricts; $i++) {
        $districts[$i] = [];
        foreach ($buildingTypes as $building) {
            $districts[$i][$building] = rand(0, 1);
        }
    }

    return $districts;
}

/**
 * Returns a static example dataset from the example.
 *
 * @return array District data.
 */
function getStaticExampleData(): array
{
    return [
        0 => ['hospital' => 1, 'school' => 0, 'restaurant' => 0, 'gym' => 1],
        1 => ['hospital' => 0, 'school' => 0, 'restaurant' => 0, 'gym' => 0],
        2 => ['hospital' => 1, 'school' => 1, 'restaurant' => 1, 'gym' => 0],
        3 => ['hospital' => 0, 'school' => 0, 'restaurant' => 1, 'gym' => 0],
        4 => ['hospital' => 1, 'school' => 0, 'restaurant' => 1, 'gym' => 1],
    ];
}

/**
 * Finds the optimal district where the maximum distance to any required building type is minimized.
 *
 * @param array $districtData Array of districts with building availability.
 * @return array Index of the optimal district.
 */
function findOptimalDistrict(array $districtData): array
{
    $startMemory = memory_get_usage();
    $startTime = microtime(true);

    $numDistricts = count($districtData);
    $buildingTypes = array_keys($districtData[0]);
    $nearestDistances = [];

    foreach ($buildingTypes as $building) {
        $nearestDistances[$building] = array_fill(0, $numDistricts, PHP_INT_MAX);
        $lastSeen = -1;

        // Left to right pass
        for ($i = 0; $i < $numDistricts; $i++) {
            if ($districtData[$i][$building] === 1) {
                $lastSeen = $i;
            }
            if ($lastSeen !== -1) {
                $nearestDistances[$building][$i] = $i - $lastSeen;
            }
        }

        $lastSeen = -1;

        // Right to left pass
        for ($i = $numDistricts - 1; $i >= 0; $i--) {
            if ($districtData[$i][$building] === 1) {
                $lastSeen = $i;
            }
            if ($lastSeen !== -1 && $nearestDistances[$building][$i] > $lastSeen - $i) {
                $nearestDistances[$building][$i] = $lastSeen - $i;
            }
        }
    }

    $optimalDistrict = -1;
    $minMaxDistance = PHP_INT_MAX;

    // Determine the optimal district
    for ($i = 0; $i < $numDistricts; $i++) {
        $maxDistance = 0;
        foreach ($buildingTypes as $building) {
            $maxDistance = max($maxDistance, $nearestDistances[$building][$i]);
        }
        if ($maxDistance < $minMaxDistance) {
            $minMaxDistance = $maxDistance;
            $optimalDistrict = $i;
        }
    }

    $endMemory = memory_get_usage();
    $peakMemory = memory_get_peak_usage();

    return [
        'optimal_district_index' => $optimalDistrict,
        'optimal_district_object' => $districtData[$optimalDistrict],
        'min_max_distance' => $minMaxDistance,
        'execution_time' => round((microtime(true) - $startTime) * 1000, 4), // Execution time in milliseconds
        'memory_used' => round(($endMemory - $startMemory) / 1024, 2), // Memory in KB
        'peak_memory' => round($peakMemory / 1024, 2) // Memory in KB
    ];

}

//$districtData = getStaticExampleData(); // Example dataset
$districtData = generateDistrictData(40000); // Uncomment to generate large dataset for testing

if (empty($districtData)) {
    throw new InvalidArgumentException("District data cannot be empty.");
}

$optimalDistrict = findOptimalDistrict($districtData);
print_r($optimalDistrict);
