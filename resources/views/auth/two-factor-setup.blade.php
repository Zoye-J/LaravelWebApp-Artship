<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Two-Factor Authentication Setup') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if(session('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if(!$isEnabled)
                        <div class="mb-6">
                            <h3 class="text-lg font-medium mb-2">Step 1: Scan QR Code</h3>
                            <p class="text-sm text-gray-600 mb-2">
                                Scan this QR code with your authenticator app (Google Authenticator, Authy, etc.)
                            </p>
                            <div class="bg-gray-100 p-4 rounded-lg inline-block">
                                <!-- QR Code placeholder - use a library or service -->
                                <div class="bg-white p-2 rounded">
                                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ urlencode('otpauth://totp/Artship:' . auth()->user()->email . '?secret=' . $secret . '&issuer=Artship') }}" 
                                         alt="QR Code">
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">
                                Or manually enter this secret: <strong>{{ $secret }}</strong>
                            </p>
                        </div>

                        <div class="mb-6">
                            <h3 class="text-lg font-medium mb-2">Step 2: Verify Code</h3>
                            <form method="POST" action="{{ route('2fa.enable') }}">
                                @csrf
                                <div>
                                    <label for="code" class="block text-sm font-medium text-gray-700">Enter 6-digit code</label>
                                    <input type="text" name="code" id="code" 
                                           class="mt-1 block w-40 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                           placeholder="123456" required>
                                </div>
                                <button type="submit" class="mt-4 bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                                    Enable 2FA
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                            ✅ Two-factor authentication is <strong>enabled</strong> on your account.
                        </div>
                        <form method="POST" action="{{ route('2fa.disable') }}" onsubmit="return confirm('Are you sure you want to disable 2FA?')">
                            @csrf
                            <div class="mt-4">
                                <label for="password" class="block text-sm font-medium text-gray-700">Enter your password to confirm:</label>
                                <input type="password" name="password" id="password" 
                                       class="mt-1 block w-64 border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500"
                                       required>
                            </div>
                            <button type="submit" class="mt-4 bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700">
                                Disable 2FA
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>