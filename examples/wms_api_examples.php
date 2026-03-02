<?php

/**
 * Example usage of WMS Pickup API
 * 
 * This file demonstrates how to use the WMS Pickup API endpoints
 */

// Example 1: Send Truck Plan Data
// ================================

$pickupData = [
    "Pickuptime" => "2025-10-20 04:00",
    "TruckPlan" => [
        [
            "MainOrder" => "SMM260108225455",
            "Location" => "A0-1"
        ],
        [
            "MainOrder" => "SMM260108225456",
            "Location" => "A1-1"
        ],
        [
            "MainOrder" => "SMM260108225457",
            "Location" => "A2-1"
        ],
        [
            "MainOrder" => "SMM260108225458",
            "Location" => "A3-1"
        ]
    ]
];

// Using cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://localhost/api/wms/truck-plan");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($pickupData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json"
]);

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);

// Print the result
echo "Update Truck Plan Response:\n";
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

/*
Expected Response (Success):
{
  "status": "success",
  "message": "Truck plan processed",
  "log_id": 1,
  "summary": {
    "total_orders": 4,
    "updated": 4,
    "failed": 0,
    "pickup_time": "2025-10-20 04:00",
    "process_status": "success"
  },
  "failed_orders": []
}
*/


// Example 2: Get All Pickup Logs
// ==============================

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://localhost/api/wms/logs?status=success&per_page=10");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);

echo "Get Logs Response:\n";
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";


// Example 3: Get Log Detail
// ==========================

$logId = 1; // Replace with actual log ID

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://localhost/api/wms/logs/" . $logId);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);

echo "Get Log Detail Response:\n";
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";


// Example 4: Get Logs with Date Range Filter
// ============================================

$from_date = "2025-01-01 00:00:00";
$to_date = "2025-12-31 23:59:59";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://localhost/api/wms/logs?from_date=" . urlencode($from_date) . "&to_date=" . urlencode($to_date));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);

echo "Get Logs with Date Range Response:\n";
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";


// Example 5: Using GuzzleHttp (if installed)
// ===========================================

/*
// For modern Laravel projects with Guzzle
use GuzzleHttp\Client;

$client = new Client();

// Send Truck Plan
$response = $client->post('/api/wms/truck-plan', [
    'json' => [
        'Pickuptime' => '2025-10-20 04:00',
        'TruckPlan' => [
            ['MainOrder' => 'SMM260108225455', 'Location' => 'A0-1'],
            ['MainOrder' => 'SMM260108225456', 'Location' => 'A1-1'],
        ]
    ]
]);

$result = json_decode($response->getBody(), true);
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

// Get Logs
$response = $client->get('/api/wms/logs', [
    'query' => [
        'status' => 'success',
        'per_page' => 20
    ]
]);

$result = json_decode($response->getBody(), true);
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

// Get Log Detail
$response = $client->get('/api/wms/logs/1');
$result = json_decode($response->getBody(), true);
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
*/


// Example 6: Error Handling
// ==========================

function updateTruckPlanWithErrorHandling($pickupData)
{
    try {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://localhost/api/wms/truck-plan");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($pickupData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json"
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return [
                'status' => 'error',
                'message' => 'Connection error: ' . $error,
                'http_code' => 0
            ];
        }

        $result = json_decode($response, true);
        $result['http_code'] = $httpCode;

        // Check if request was successful
        if ($httpCode == 200 && isset($result['status']) && $result['status'] == 'success') {
            return [
                'success' => true,
                'data' => $result,
                'http_code' => $httpCode
            ];
        } else if ($httpCode == 422) {
            return [
                'success' => false,
                'error' => 'Validation failed',
                'details' => $result['errors'] ?? [],
                'http_code' => $httpCode
            ];
        } else {
            return [
                'success' => false,
                'error' => $result['message'] ?? 'Unknown error',
                'http_code' => $httpCode
            ];
        }
    } catch (Exception $e) {
        return [
            'status' => 'error',
            'message' => $e->getMessage()
        ];
    }
}

// Usage
$testData = [
    "Pickuptime" => "2025-10-20 04:00",
    "TruckPlan" => [
        ["MainOrder" => "SMM260108225455", "Location" => "A0-1"],
    ]
];

$result = updateTruckPlanWithErrorHandling($testData);
echo "Error Handling Result:\n";
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
