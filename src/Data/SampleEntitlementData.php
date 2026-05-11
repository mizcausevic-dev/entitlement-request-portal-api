<?php

declare(strict_types=1);

final class SampleEntitlementData
{
    /**
     * @return EntitlementRequest[]
     */
    public function requests(): array
    {
        return [
            new EntitlementRequest(
                'er-4018',
                'Mia Holloway',
                'Revenue Operations',
                'Salesforce',
                'Opportunity Delete + Stage Override',
                'high',
                'privilege-elevation',
                '2026-05-10T12:20:00Z',
                'watch',
                'identity-governance',
                24,
                true,
                true,
                false,
                [
                    new ApprovalStep('Manager review', 'approved', 'Lena Ortiz', '2026-05-10T13:02:00Z'),
                    new ApprovalStep('System owner', 'pending', 'Drew Patel', '2026-05-10T13:25:00Z'),
                    new ApprovalStep('Security review', 'queued', 'Marta Bell', '2026-05-10T13:25:00Z'),
                ],
                [
                    new PolicyFinding('segregation-of-duties', 'high', 'Delete and stage override rights sit in a sales-owned production lane.'),
                    new PolicyFinding('sox-change-scope', 'medium', 'Requested entitlement touches an in-scope revenue control system.'),
                ]
            ),
            new EntitlementRequest(
                'er-4027',
                'Omar Keita',
                'Customer Success',
                'Zendesk',
                'Bulk export and ticket redaction bypass',
                'critical',
                'exception',
                '2026-05-10T09:05:00Z',
                'escalate',
                'service-ops',
                12,
                false,
                true,
                true,
                [
                    new ApprovalStep('Manager review', 'approved', 'Jillian Ross', '2026-05-10T09:41:00Z'),
                    new ApprovalStep('System owner', 'approved', 'Noah Singh', '2026-05-10T10:04:00Z'),
                    new ApprovalStep('Security review', 'stalled', 'Ivy Grant', '2026-05-10T11:22:00Z'),
                ],
                [
                    new PolicyFinding('contractor-privilege', 'critical', 'Contractor account is requesting bypass controls on customer support exports.'),
                    new PolicyFinding('data-minimization', 'high', 'Requested access removes current redaction gate on personally identifiable data.'),
                ]
            ),
            new EntitlementRequest(
                'er-4031',
                'Caleb Morse',
                'Platform Engineering',
                'Datadog',
                'Read-only observability access',
                'low',
                'new-access',
                '2026-05-10T15:12:00Z',
                'stable',
                'platform-access',
                36,
                false,
                false,
                false,
                [
                    new ApprovalStep('Manager review', 'approved', 'Nina Faulk', '2026-05-10T15:35:00Z'),
                    new ApprovalStep('System owner', 'approved', 'Arun Das', '2026-05-10T16:08:00Z'),
                ],
                []
            ),
        ];
    }
}

