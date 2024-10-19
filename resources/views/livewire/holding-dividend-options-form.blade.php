<?php

use App\Models\Holding;
use Illuminate\Support\Collection;
use Livewire\Attributes\{Computed};
use Livewire\Volt\Component;
use Mary\Traits\Toast;
use Illuminate\Validation\Rule;

new class extends Component {
    use Toast;

    // props
    public Holding $holding;

    public Bool $reinvest_dividends = false;

    // methods
    public function rules()
    {

        return [
            'reinvest_dividends' => ['required', 'boolean'],
        ];
    }

    public function mount() 
    {
        
        $this->reinvest_dividends = $this->holding?->reinvest_dividends ?? false;
    }

    public function save()
    {
        $this->holding->update($this->validate());

        $this->success(__('Dividend options saved'));

        $this->dispatch('toggle-dividend-options');
    }
}; ?>

<div class="" x-data="{ }">
    <x-ib-form wire:submit="save" class="">

        <x-toggle 
            label="{{ __('Reinvest dividends') }}" 
            wire:model="reinvest_dividends" 
            right 
            hint="{{ __('Automatically generate buy transactions for any dividends earned.') }}"
        />

        <x-slot:actions>

            <x-button 
                label="{{ __('Save') }}" 
                type="submit" 
                icon="o-paper-airplane" 
                class="btn-primary" 
                spinner="save"
            />
        </x-slot:actions>
    </x-ib-form>

</div>