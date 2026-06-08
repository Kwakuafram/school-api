<?php

namespace Tests\Feature\Models;

use App\Models\AuditLog;
use App\Models\Campus;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FoundationModelsTest extends TestCase
{
    use RefreshDatabase;

    public function test_school_model_can_be_created_with_uuid_and_relationships(): void
    {
        $school = School::query()->create([
            'name' => 'Demo International School',
            'code' => 'DIS',
            'email' => 'info@demo-school.test',
            'phone' => '+233000000000',
            'country' => 'Ghana',
            'region' => 'Ashanti',
            'city' => 'Kumasi',
            'address' => 'Main Campus',
            'status' => 'active',
            'settings' => [
                'timezone' => 'Africa/Accra',
                'currency' => 'GHS',
            ],
        ]);

        $this->assertNotNull($school->id);
        $this->assertIsString($school->id);
        $this->assertSame('DIS', $school->code);
        $this->assertSame('Africa/Accra', $school->settings['timezone']);
        $this->assertTrue($school->campuses()->exists() === false);
    }

    public function test_campus_model_belongs_to_school_and_uses_uuid(): void
    {
        $school = $this->createSchool();

        $campus = Campus::query()->create([
            'school_id' => $school->id,
            'name' => 'Main Campus',
            'code' => 'MAIN',
            'email' => 'main@demo-school.test',
            'phone' => '+233000000001',
            'country' => 'Ghana',
            'region' => 'Ashanti',
            'city' => 'Kumasi',
            'address' => 'Main Campus Address',
            'is_main' => true,
            'status' => 'active',
            'settings' => [
                'opening_time' => '07:00',
            ],
        ]);

        $this->assertNotNull($campus->id);
        $this->assertIsString($campus->id);
        $this->assertTrue($campus->is_main);
        $this->assertSame($school->id, $campus->school->id);
        $this->assertSame('07:00', $campus->settings['opening_time']);
    }

    public function test_user_model_hashes_password_and_belongs_to_schools(): void
    {
        $school = $this->createSchool();

        $user = User::query()->create([
            'name' => 'School Admin',
            'email' => 'admin@demo-school.test',
            'password' => 'password123',
            'status' => 'active',
        ]);

        $user->schools()->attach($school->id, [
            'role_context' => 'school_admin',
            'is_default' => true,
        ]);

        $user->refresh()->load('schools');

        $this->assertNotNull($user->id);
        $this->assertIsString($user->id);
        $this->assertTrue(Hash::check('password123', $user->password));
        $this->assertCount(1, $user->schools);
        $this->assertSame('school_admin', $user->schools->first()->pivot->role_context);
        $this->assertTrue((bool) $user->schools->first()->pivot->is_default);
    }

    public function test_user_can_receive_spatie_role_and_sanctum_token_with_uuid_id(): void
    {
        Role::query()->firstOrCreate([
            'name' => 'super_admin',
            'guard_name' => 'web',
        ]);

        Permission::query()->firstOrCreate([
            'name' => 'schools.view',
            'guard_name' => 'web',
        ]);

        $user = User::query()->create([
            'name' => 'System Super Admin',
            'email' => 'admin@sms.local',
            'password' => 'password123',
            'status' => 'active',
        ]);

        $user->assignRole('super_admin');

        $token = $user->createToken('phpunit')->plainTextToken;

        $this->assertTrue($user->hasRole('super_admin'));
        $this->assertNotEmpty($token);
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_type' => User::class,
            'tokenable_id' => $user->id,
            'name' => 'phpunit',
        ]);

        $storedToken = PersonalAccessToken::query()
            ->where('tokenable_id', $user->id)
            ->first();

        $this->assertNotNull($storedToken);
        $this->assertSame($user->id, $storedToken->tokenable_id);
    }

    public function test_audit_log_model_can_reference_school_campus_user_and_auditable(): void
    {
        $school = $this->createSchool();
        $campus = $this->createCampus($school);
        $user = $this->createUser();

        $auditLog = AuditLog::query()->create([
            'school_id' => $school->id,
            'campus_id' => $campus->id,
            'user_id' => $user->id,
            'action' => 'campus.created',
            'auditable_type' => Campus::class,
            'auditable_id' => $campus->id,
            'old_values' => null,
            'new_values' => [
                'name' => 'Main Campus',
            ],
            'metadata' => [
                'source' => 'phpunit',
            ],
            'ip_address' => '127.0.0.1',
            'user_agent' => 'phpunit',
        ]);

        $this->assertNotNull($auditLog->id);
        $this->assertSame($school->id, $auditLog->school->id);
        $this->assertSame($campus->id, $auditLog->campus->id);
        $this->assertSame($user->id, $auditLog->user->id);
        $this->assertSame('Main Campus', $auditLog->new_values['name']);
        $this->assertSame('phpunit', $auditLog->metadata['source']);
        $this->assertInstanceOf(Campus::class, $auditLog->auditable);
    }

    private function createSchool(array $overrides = []): School
    {
        return School::query()->create(array_merge([
            'name' => 'Demo International School',
            'code' => 'DIS'.uniqid(),
            'email' => 'info'.uniqid().'@demo-school.test',
            'status' => 'active',
        ], $overrides));
    }

    private function createCampus(School $school, array $overrides = []): Campus
    {
        return Campus::query()->create(array_merge([
            'school_id' => $school->id,
            'name' => 'Main Campus',
            'code' => 'MAIN'.uniqid(),
            'is_main' => true,
            'status' => 'active',
        ], $overrides));
    }

    private function createUser(array $overrides = []): User
    {
        return User::query()->create(array_merge([
            'name' => 'Test User',
            'email' => 'user'.uniqid().'@demo-school.test',
            'password' => 'password123',
            'status' => 'active',
        ], $overrides));
    }
}
