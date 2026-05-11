<?php

declare(strict_types=1);

final class EntitlementAnalysisService
{
    public function __construct(private readonly SampleEntitlementData $data)
    {
    }

    public function summary(): array
    {
        $requests = $this->data->requests();

        $critical = 0;
        $watch = 0;
        $pendingApprovals = 0;
        $privileged = 0;

        foreach ($requests as $request) {
            if ($request->status === 'escalate') {
                $critical++;
            } elseif ($request->status === 'watch') {
                $watch++;
            }

            if ($request->privileged) {
                $privileged++;
            }

            foreach ($request->approvals as $approval) {
                if (in_array($approval->state, ['pending', 'queued', 'stalled'], true)) {
                    $pendingApprovals++;
                }
            }
        }

        return [
            'tracked_requests' => count($requests),
            'critical_requests' => $critical,
            'watch_requests' => $watch,
            'pending_approval_steps' => $pendingApprovals,
            'privileged_requests' => $privileged,
        ];
    }

    public function requests(): array
    {
        return array_map(
            fn (EntitlementRequest $request) => $this->serializeRequest($request),
            $this->data->requests()
        );
    }

    public function requestById(string $requestId): ?array
    {
        foreach ($this->data->requests() as $request) {
            if ($request->requestId === $requestId) {
                return $this->serializeRequest($request);
            }
        }

        return null;
    }

    public function analyze(array $payload): array
    {
        $severity = $payload['sensitivity'] ?? 'medium';
        $privileged = (bool) ($payload['privileged'] ?? false);
        $soxScoped = (bool) ($payload['sox_scoped'] ?? false);
        $contractor = (bool) ($payload['contractor_access'] ?? false);
        $approvalLagHours = (int) ($payload['approval_lag_hours'] ?? 0);

        $score = 22;

        if ($severity === 'critical') {
            $score += 34;
        } elseif ($severity === 'high') {
            $score += 24;
        }

        if ($privileged) {
            $score += 18;
        }

        if ($soxScoped) {
            $score += 14;
        }

        if ($contractor) {
            $score += 18;
        }

        if ($approvalLagHours >= 12) {
            $score += 16;
        } elseif ($approvalLagHours >= 6) {
            $score += 8;
        }

        $decision = 'stable';
        $action = 'Continue through the standard owner lane and capture the approval audit trail.';

        if ($score >= 75) {
            $decision = 'escalate';
            $action = 'Escalate to identity governance and security before granting access; freeze fulfillment until policy exceptions are resolved.';
        } elseif ($score >= 48) {
            $decision = 'watch';
            $action = 'Route into a higher-friction review lane and require system-owner confirmation before fulfillment.';
        }

        return [
            'decision' => $decision,
            'score' => $score,
            'recommended_action' => $action,
        ];
    }

    private function serializeRequest(EntitlementRequest $request): array
    {
        return [
            'request_id' => $request->requestId,
            'requester' => $request->requester,
            'business_unit' => $request->businessUnit,
            'target_system' => $request->targetSystem,
            'entitlement_name' => $request->entitlementName,
            'sensitivity' => $request->sensitivity,
            'request_type' => $request->requestType,
            'submitted_at' => $request->submittedAt,
            'status' => $request->status,
            'owner_lane' => $request->ownerLane,
            'sla_hours' => $request->slaHours,
            'sox_scoped' => $request->soxScoped,
            'privileged' => $request->privileged,
            'contractor_access' => $request->contractorAccess,
            'approvals' => array_map(
                static fn (ApprovalStep $step) => [
                    'name' => $step->name,
                    'state' => $step->state,
                    'owner' => $step->owner,
                    'updated_at' => $step->updatedAt,
                ],
                $request->approvals
            ),
            'policy_findings' => array_map(
                static fn (PolicyFinding $finding) => [
                    'code' => $finding->code,
                    'severity' => $finding->severity,
                    'message' => $finding->message,
                ],
                $request->policyFindings
            ),
        ];
    }
}

