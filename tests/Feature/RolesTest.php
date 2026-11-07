<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\DailyReport;
use App\Models\EggCollection;
use App\Models\Input;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RolesTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_accounts_are_simple_users_by_default(): void
    {
        $user = User::create(['name' => 'Gardien', 'email' => 'gardien@example.com', 'password' => 'secret']);

        $this->assertSame(UserRole::Simple, $user->fresh()->role);
        $this->assertFalse($user->fresh()->isAdmin());
    }

    public function test_simple_user_can_use_reports_inputs_and_eggs(): void
    {
        $report = DailyReport::factory()->create();
        $input = Input::factory()->create();
        $collection = EggCollection::factory()->create();

        $this->actingAs(User::factory()->create());

        foreach ([
            route('dashboard'),
            route('reports.index'),
            route('reports.create'),
            route('reports.show', $report),
            route('inputs.index'),
            route('inputs.create'),
            route('inputs.show', $input),
            route('inputs.movements.create', $input),
            route('eggs.collections.index'),
            route('eggs.collections.show', $collection),
            route('eggs.sales.index'),
            route('eggs.sales.create'),
        ] as $url) {
            $this->get($url)->assertOk();
        }

        $this->post(route('eggs.collections.store'), [
            'collection_date' => '2026-11-06',
            'collected_count' => 300,
        ])->assertRedirect(route('eggs.collections.index'));
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function accountingRoutes(): array
    {
        return [
            'tableau compta' => ['get', 'accounting.index'],
            'formulaire écriture' => ['get', 'transactions.create'],
            'création écriture' => ['post', 'transactions.store'],
        ];
    }

    #[DataProvider('accountingRoutes')]
    public function test_simple_user_cannot_access_accounting(string $method, string $routeName): void
    {
        $this->actingAs(User::factory()->create())
            ->{$method}(route($routeName), [
                'type' => 'expense',
                'category' => 'labor',
                'transaction_date' => '2026-11-01',
                'label' => 'Salaire',
                'amount' => 50000,
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_simple_user_cannot_edit_or_delete_an_existing_entry(): void
    {
        $transaction = Transaction::factory()->create();
        $this->actingAs(User::factory()->create());

        $this->get(route('transactions.edit', $transaction))->assertForbidden();
        $this->put(route('transactions.update', $transaction), ['label' => 'x'])->assertForbidden();
        $this->delete(route('transactions.destroy', $transaction))->assertForbidden();

        $this->assertModelExists($transaction);
    }

    public function test_accounting_menu_is_only_shown_to_admins(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('dashboard'))
            ->assertDontSee(route('accounting.index'));

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('dashboard'))
            ->assertSee(route('accounting.index'));
    }

    public function test_admin_can_access_accounting(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get(route('accounting.index'))
            ->assertOk();
    }

    public function test_guest_is_redirected_from_accounting(): void
    {
        $this->get(route('accounting.index'))->assertRedirect(route('login'));
    }

    public function test_role_command_promotes_a_user(): void
    {
        $user = User::factory()->create(['email' => 'patron@example.com']);

        $this->artisan('app:set-user-role', ['email' => 'patron@example.com', 'role' => 'admin'])
            ->assertSuccessful();

        $this->assertTrue($user->fresh()->isAdmin());

        $this->artisan('app:set-user-role', ['email' => 'patron@example.com', 'role' => '1'])
            ->assertSuccessful();

        $this->assertFalse($user->fresh()->isAdmin());
    }

    public function test_role_command_rejects_unknown_user_or_role(): void
    {
        User::factory()->create(['email' => 'patron@example.com']);

        $this->artisan('app:set-user-role', ['email' => 'inconnu@example.com', 'role' => 'admin'])
            ->assertFailed();

        $this->artisan('app:set-user-role', ['email' => 'patron@example.com', 'role' => 'chef'])
            ->assertExitCode(2);
    }
}
