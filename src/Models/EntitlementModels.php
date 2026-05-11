<?php

declare(strict_types=1);

final class EntitlementRequest
{
    public function __construct(
        public string $requestId,
        public string $requester,
        public string $businessUnit,
        public string $targetSystem,
        public string $entitlementName,
        public string $sensitivity,
        public string $requestType,
        public string $submittedAt,
        public string $status,
        public string $ownerLane,
        public int $slaHours,
        public bool $soxScoped,
        public bool $privileged,
        public bool $contractorAccess,
        public array $approvals,
        public array $policyFindings
    ) {
    }
}

final class ApprovalStep
{
    public function __construct(
        public string $name,
        public string $state,
        public string $owner,
        public string $updatedAt
    ) {
    }
}

final class PolicyFinding
{
    public function __construct(
        public string $code,
        public string $severity,
        public string $message
    ) {
    }
}

