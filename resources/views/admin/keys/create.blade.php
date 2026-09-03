<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Generate New Key') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                @if ($errors->any())
                    <div class="mb-4 p-4 bg-red-100 border border-red-300 text-red-800 rounded">
                        <ul class="list-disc list-inside text-sm">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.keys.store') }}" x-data="{ algorithm: 'rsa' }">
                    @csrf

                    <div class="mb-4">
                        <x-input-label for="algorithm" value="Algorithm" />
                        <select id="algorithm" name="algorithm" x-model="algorithm"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            <option value="rsa">RSA</option>
                            <option value="ecc">ECC (secp256k1)</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <x-input-label for="purpose" value="Purpose" />
                        <x-text-input id="purpose" name="purpose" type="text" class="mt-1 block w-full"
                                      placeholder="e.g. encryption, signing" value="{{ old('purpose') }}" required />
                        <p class="text-xs text-gray-500 mt-1">
                            A free-form label so multiple keys can coexist (e.g. RSA for signing, ECC for bulk content encryption).
                        </p>
                    </div>

                    <div class="mb-6" x-show="algorithm === 'rsa'">
                        <x-input-label for="key_size" value="RSA Key Size" />
                        <select id="key_size" name="key_size" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            <option value="1024">1024 bits (fast — testing only)</option>
                            <option value="2048" selected>2048 bits (recommended)</option>
                            <option value="3072">3072 bits</option>
                            <option value="4096">4096 bits (slow to generate)</option>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Not used for ECC — secp256k1 has a fixed 256-bit key size.</p>
                    </div>

                    <div class="flex items-center justify-end gap-3">
                        <a href="{{ route('admin.keys.index') }}" class="text-sm text-gray-600 hover:underline">Cancel</a>
                        <x-primary-button>{{ __('Generate Key') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
