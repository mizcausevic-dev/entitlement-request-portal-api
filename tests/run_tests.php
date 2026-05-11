<?php

declare(strict_types=1);

require __DIR__ . '/../src/bootstrap.php';

$service = new EntitlementAnalysisService(new SampleEntitlementData());

$summary = $service->summary();
if (($summary['tracked_requests'] ?? 0) !== 3) {
    fwrite(STDERR, "Expected 3 tracked requests.\n");
    exit(1);
}

$request = $service->requestById('er-4027');
if ($request === null || $request['status'] !== 'escalate') {
    fwrite(STDERR, "Expected er-4027 to be present and escalated.\n");
    exit(1);
}

$analysis = $service->analyze([
    'sensitivity' => 'critical',
    'privileged' => true,
    'sox_scoped' => true,
    'contractor_access' => true,
    'approval_lag_hours' => 12,
]);

if (($analysis['decision'] ?? '') !== 'escalate') {
    fwrite(STDERR, "Expected critical request to escalate.\n");
    exit(1);
}

echo "All entitlement-request-portal-api tests passed.\n";

