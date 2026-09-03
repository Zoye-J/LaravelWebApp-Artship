<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Key Management') }}
            </h2>
            <a href="{{ route('admin.keys.create') }}"
               class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                {{ __('Generate New Key') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (session('status'))
                <div class="mb-4 p-4 bg-green-100 border border-green-300 text-green-800 rounded">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Algorithm</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Purpose</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Size</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Version</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Fingerprint</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Created</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($keys as $key)
                                <tr>
                                    <td class="px-4 py-3">{{ $key->id }}</td>
                                    <td class="px-4 py-3 uppercase font-semibold">{{ $key->algorithm }}</td>
                                    <td class="px-4 py-3">{{ $key->purpose }}</td>
                                    <td class="px-4 py-3">{{ $key->key_size ? $key->key_size . ' bits' : 'secp256k1' }}</td>
                                    <td class="px-4 py-3">v{{ $key->version }}</td>
                                    <td class="px-4 py-3 font-mono text-xs" title="{{ $key->fingerprint }}">
                                        {{ substr($key->fingerprint, 0, 16) }}&hellip;
                                    </td>
                                    <td class="px-4 py-3">
                                        @php
                                            $badge = [
                                                'active' => 'bg-green-100 text-green-800',
                                                'rotated' => 'bg-yellow-100 text-yellow-800',
                                                'revoked' => 'bg-red-100 text-red-800',
                                            ][$key->status] ?? 'bg-gray-100 text-gray-800';
                                        @endphp
                                        <span class="px-2 py-1 rounded text-xs font-semibold {{ $badge }}">
                                            {{ ucfirst($key->status) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">{{ $key->created_at->format('Y-m-d H:i') }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <a href="{{ route('admin.keys.show', $key) }}" class="text-indigo-600 hover:underline">View</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-4 py-6 text-center text-gray-500">
                                        No keys yet. <a href="{{ route('admin.keys.create') }}" class="text-indigo-600 hover:underline">Generate the first one</a>.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-4">
                {{ $keys->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
