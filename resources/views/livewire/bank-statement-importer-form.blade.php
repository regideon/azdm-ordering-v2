<div>
    <form wire:submit.prevent="submit">
        @if (session()->has('success'))
            <div style="margin-top: 16px; padding: 12px 16px; background-color: #d1fae5; border: 1px solid #10b981; color: #065f46; border-radius: 6px; margin-bottom: 16px">
                {{ session('success') }}
            </div>
        @endif

        {{ $this->form }}

        <x-filament::button type="submit">
            Upload and Import
        </x-filament::button>
    </form>
</div>
