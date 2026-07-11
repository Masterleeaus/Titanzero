<?php

namespace Modules\TitanZero\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Modules\TitanZero\Services\Security\CrossTenantGuard;

/**
 * Unit tests for CrossTenantGuard — verifies tenant isolation enforcement.
 */
class CrossTenantGuardTest extends TestCase
{
    public function test_same_tenant_does_not_throw(): void
    {
        $this->expectNotToPerformAssertions();
        CrossTenantGuard::assertSameTenant(1, 1, 'Lead');
    }

    public function test_different_tenant_throws_runtime_exception(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/Cross-tenant violation/');

        CrossTenantGuard::assertSameTenant(1, 2, 'Lead');
    }

    public function test_context_with_company_id_passes(): void
    {
        $this->expectNotToPerformAssertions();
        CrossTenantGuard::assertContextContainsTenant(['company_id' => 5]);
    }

    public function test_context_missing_company_id_throws(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/missing a required company_id/');

        CrossTenantGuard::assertContextContainsTenant(['user_id' => 1]);
    }

    public function test_context_with_null_company_id_throws(): void
    {
        $this->expectException(\RuntimeException::class);

        CrossTenantGuard::assertContextContainsTenant(['company_id' => null]);
    }

    public function test_collection_same_tenant_passes(): void
    {
        $this->expectNotToPerformAssertions();
        CrossTenantGuard::assertCollectionSameTenant(1, [
            ['company_id' => 1],
            ['company_id' => 1],
        ]);
    }

    public function test_collection_with_wrong_tenant_throws(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/Cross-tenant violation/');

        CrossTenantGuard::assertCollectionSameTenant(1, [
            ['company_id' => 1],
            ['company_id' => 2],
        ]);
    }

    public function test_collection_with_missing_company_id_throws(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/no company_id set/');

        CrossTenantGuard::assertCollectionSameTenant(1, [
            ['name' => 'record without company_id'],
        ]);
    }

    public function test_object_records_are_accepted(): void
    {
        $record = new \stdClass();
        $record->company_id = 5;

        $this->expectNotToPerformAssertions();
        CrossTenantGuard::assertCollectionSameTenant(5, [$record]);
    }

    public function test_object_record_wrong_tenant_throws(): void
    {
        $record = new \stdClass();
        $record->company_id = 9;

        $this->expectException(\RuntimeException::class);

        CrossTenantGuard::assertCollectionSameTenant(5, [$record]);
    }
}
