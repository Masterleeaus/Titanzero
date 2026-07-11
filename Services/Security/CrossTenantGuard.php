<?php

namespace Modules\TitanZero\Services\Security;

use RuntimeException;

/**
 * CrossTenantGuard — asserts that a tool execution never accesses data outside
 * the resolved company_id boundary (Blueprint 19 requirement).
 *
 * Usage:
 *   CrossTenantGuard::assertSameTenant($resolvedCompanyId, $recordCompanyId, 'LeadRecord');
 *   CrossTenantGuard::assertContextContainsTenant($context);
 */
class CrossTenantGuard
{
    /**
     * Assert that a record's company_id matches the resolved execution tenant.
     *
     * @throws RuntimeException when tenant boundaries are violated.
     */
    public static function assertSameTenant(
        int    $resolvedCompanyId,
        int    $recordCompanyId,
        string $resourceLabel = 'resource',
    ): void {
        if ($resolvedCompanyId !== $recordCompanyId) {
            throw new RuntimeException(
                "Cross-tenant violation: {$resourceLabel} belongs to company {$recordCompanyId} "
                . "but execution context is company {$resolvedCompanyId}."
            );
        }
    }

    /**
     * Assert that the tool execution context array contains a non-null company_id.
     *
     * @throws RuntimeException when company_id is absent or null.
     */
    public static function assertContextContainsTenant(array $context): void
    {
        if (empty($context['company_id'])) {
            throw new RuntimeException(
                'Cross-tenant guard: tool execution context is missing a required company_id.'
            );
        }
    }

    /**
     * Assert that all records in a collection belong to the resolved tenant.
     *
     * @param  iterable  $records  Objects or arrays with a company_id property/key.
     * @throws RuntimeException on first violation.
     */
    public static function assertCollectionSameTenant(
        int      $resolvedCompanyId,
        iterable $records,
        string   $resourceLabel = 'record',
    ): void {
        foreach ($records as $record) {
            $id = is_array($record) ? ($record['company_id'] ?? null) : ($record->company_id ?? null);

            if ($id === null) {
                throw new RuntimeException(
                    "Cross-tenant guard: {$resourceLabel} has no company_id set."
                );
            }

            self::assertSameTenant($resolvedCompanyId, (int) $id, $resourceLabel);
        }
    }
}
