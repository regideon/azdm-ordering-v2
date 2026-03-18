<x-filament-panels::page>
    <div class="overflow-x-auto">
        <x-filament-panels::form wire:submit="save">
            {{ $this->form }}
            <x-filament-panels::form.actions :actions="$this->getFormActions()" />
        </x-filament-panels::form>

        <div class="w-full md:w-full lg:w-3/4 mx-auto mt-5">
            <div>&nbsp;</div>
            <table class="min-w-full table-auto rounded-lg shadow-lg overflow-hidden">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="p-4 text-left text-gray-600 font-bold text-sm">#</th>
                        <th class="p-4 text-left text-gray-600 font-bold text-sm">Customer Name</th>
                        <th class="p-4 text-left text-gray-600 font-bold text-sm">Total Orders</th>
                        <th class="p-4 text-left text-gray-600 font-bold text-sm">Total Spent</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($records as $key => $item)
                        <tr class="bg-white border-t">
                            <td class="p-4 text-gray-900 text-sm">{{ $key + 1 }}</td>
                            <td class="p-4 text-gray-900 text-sm @if ($key < 3) font-bold @endif">{{ $item->customer_name }}</td>
                            <td class="p-4 text-gray-900 text-sm">{{ $item->total_orders }}</td>
                            <td class="p-4 text-gray-900 text-sm">₱{{ number_format($item->total_spent, 2, '.', ',') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-filament-panels::page>
