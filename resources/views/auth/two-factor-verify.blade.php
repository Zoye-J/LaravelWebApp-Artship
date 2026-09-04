<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('Please enter your two-factor authentication code to continue.') }}
    </div>

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    @if(session('info'))
        <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded mb-4">
            {{ session('info') }}
        </div>
    @endif

    <form method="POST" action="{{ route('2fa.verify.post') }}">
        @csrf
        <div>
            <label for="code" class="block text-sm font-medium text-gray-700 mb-1">
                Authentication Code
            </label>
            <input id="code" type="text" name="code" 
                   class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                   placeholder="Enter 6-digit code or backup code" required autofocus>
            <x-input-error :messages="$errors->get('code')" class="mt-2" />
        </div>

        <div class="mt-4">
            <button type="submit" class="w-full bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                Verify
            </button>
        </div>
    </form>
</x-guest-layout>