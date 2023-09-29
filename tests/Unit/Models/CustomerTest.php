<?php

namespace Lunar\Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Lunar\Models\Address;
use Lunar\Models\Customer;
use Lunar\Models\CustomerGroup;
use Lunar\Tests\Stubs\User;
use Lunar\Tests\TestCase;

/**
 * @group lunar.models
 */
class CustomerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_make_a_customer_with_minimum_attributes()
    {
        $customer = [
            'title' => null,
            'first_name' => 'Tony',
            'last_name' => 'Stark',
            'company_name' => null,
            'vat_no' => null,
            'meta' => null,
        ];

        Customer::create($customer);

        $customer['meta'] = json_encode($customer['meta']);

        $this->assertDatabaseHas(
            'lunar_customers',
            $customer
        );
    }

    /** @test */
    public function can_make_a_customer()
    {
        $customer = [
            'title' => 'Mr.',
            'first_name' => 'Tony',
            'last_name' => 'Stark',
            'company_name' => 'Stark Enterprises',
            'vat_no' => null,
            'meta' => null,
        ];

        Customer::create($customer);

        $customer['meta'] = json_encode($customer['meta']);

        $this->assertDatabaseHas(
            'lunar_customers',
            $customer
        );
    }

    /** @test */
    public function can_make_a_customer_with_meta_attribute()
    {
        $customer = [
            'title' => null,
            'first_name' => 'Tony',
            'last_name' => 'Stark',
            'company_name' => null,
            'vat_no' => null,
            'meta' => [
                'account' => 123456,
            ],
        ];

        $customer = Customer::create($customer);

        $this->assertEquals(123456, $customer->meta['account']);
    }

    /** @test */
    public function can_get_full_name()
    {
        $customer = Customer::factory()->create([
            'title' => null,
            'first_name' => 'Tony',
            'last_name' => 'Stark',
        ]);

        $this->assertEquals(
            "$customer->first_name $customer->last_name",
            $customer->fullName
        );

        $customer = Customer::factory()->create([
            'title' => 'Mr.',
            'first_name' => 'Tony',
            'last_name' => 'Stark',
        ]);

        $this->assertEquals(
            "$customer->title $customer->first_name $customer->last_name",
            $customer->fullName
        );

        $customer = Customer::factory()->create([
            'title' => 'Mr.',
            'first_name' => '',
            'last_name' => 'Stark',
        ]);

        $this->assertEquals(
            "$customer->title $customer->last_name",
            $customer->fullName
        );

        $customer = Customer::factory()->create([
            'title' => 'Mr.',
            'first_name' => 'Tony',
            'last_name' => '',
        ]);

        $this->assertEquals(
            "$customer->title $customer->first_name",
            $customer->fullName
        );
    }

    /** @test */
    public function can_associate_to_customer_groups()
    {
        $groups = CustomerGroup::factory(4)->create();
        $customer = Customer::factory()->create();

        $customer->customerGroups()->sync($groups->pluck('id'));

        $this->assertCount($groups->count(), $customer->customerGroups);
    }

    /** @test */
    public function can_associate_to_users()
    {
        $users = User::factory(4)->create();
        $customer = Customer::factory()->create();

        $customer->users()->sync($users->pluck('id'));

        $this->assertCount($users->count(), $customer->users);
    }

    /** @test */
    public function can_fetch_customer_addresses()
    {
        $customer = Customer::factory()->create();
        $addresses = Address::factory(2)->create([
            'customer_id' => $customer->id,
        ]);

        $this->assertCount($addresses->count(), $customer->addresses()->get());
    }

    /**
     * @test
     */
    public function can_retrieve_latest_customer()
    {
        Config::set('auth.providers.users.model', 'Lunar\Tests\Stubs\User');

        $user = User::factory()->create();

        $customers = Customer::factory(5)->create();

        $user->customers()->sync($customers->pluck('id'));

        $this->assertCount(5, $user->customers()->get());

        $this->assertDatabaseCount((new Customer)->getTable(), 5);

        $this->assertEquals($customers->last()->id, $user->latestCustomer()->id);
    }
}
