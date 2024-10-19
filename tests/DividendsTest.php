<?php

namespace Tests;

use App\Models\Dividend;
use Tests\TestCase;
use App\Models\User;
use App\Models\Split;
use App\Models\Holding;
use App\Models\Portfolio;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DividendsTest extends TestCase
{
    use RefreshDatabase;

    /**
     */
    public function test_new_dividends_update_holding(): void
    {
        $this->actingAs($user = User::factory()->create());
        
        $portfolio = Portfolio::factory()->create();
        Transaction::factory()->buy()->yearsAgo()->portfolio($portfolio->id)->symbol('ACME')->create();

        $holding = Holding::query()->portfolio($portfolio->id)->symbol('ACME')->first();
        
        $this->assertEquals(0, $holding->dividends_earned);

        Dividend::refreshDividendData('ACME');

        $holding->refresh();

        $this->assertEquals(4.95, $holding->dividends_earned);
    }

    /**
     */
    public function test_new_dividends_are_reinvested(): void
    {
        $this->actingAs($user = User::factory()->create());
        
        $portfolio = Portfolio::factory()->create();
        Transaction::factory()->buy()->yearsAgo()->portfolio($portfolio->id)->symbol('ACME')->create();

        $holding = Holding::query()->portfolio($portfolio->id)->symbol('ACME')->first();
        $holding->reinvest_dividends = true;
        $holding->save();
        
        $this->assertEquals(0, $holding->dividends_earned);

        Dividend::refreshDividendData('ACME');

        $transactions = Transaction::where(['reinvested_dividend' => true])->symbol('ACME')->portfolio($portfolio->id)->get();

        $dividendsReinvested = $transactions->reduce(function ($carry, $transaction) {
            return $carry + ($transaction->cost_basis * $transaction->quantity);
        }, 0); 

        $this->assertCount(3, $transactions);
        $this->assertEqualsWithDelta(4.95, $dividendsReinvested, 0.01);
    }
}
