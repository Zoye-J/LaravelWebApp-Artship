<x-guest-layout>
    @if($isFirstTime ?? false)
        <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
            <h3 class="text-lg font-medium text-yellow-800 mb-2">🔐 First Time Setup Required</h3>
            <p class="text-sm text-yellow-700 mb-2">
                Please scan the QR code below with your authenticator app (Google Authenticator, Authy, etc.)
            </p>
            <div class="bg-white p-4 rounded-lg inline-block">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ urlencode('otpauth://totp/Artship:' . ($user->email ?? 'user') . '?secret=' . $secret . '&issuer=Artship') }}" 
                     alt="QR Code">
            </div>
            <p class="text-xs text-gray-600 mt-2">
                Secret: <strong class="font-mono">{{ $secret }}</strong>
            </p>
            <p class="text-sm text-yellow-700 mt-2">
                After scanning, enter the 6-digit code from your app below.
            </p>
        </div>
    @endif

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

        <div class="mt-4 flex items-center justify-between">
            <button type="submit" class="w-full bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                Verify
            </button>
        </div>
    </form>
</x-guest-layout>