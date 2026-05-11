<?php

declare(strict_types=1);

require __DIR__ . '/Models/EntitlementModels.php';
require __DIR__ . '/Data/SampleEntitlementData.php';
require __DIR__ . '/Services/EntitlementAnalysisService.php';
require __DIR__ . '/Http/JsonResponse.php';
require __DIR__ . '/Http/App.php';

function create_app(): App
{
    return new App(
        new EntitlementAnalysisService(
            new SampleEntitlementData()
        )
    );
}

