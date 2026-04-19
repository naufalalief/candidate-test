<x-guest-layout>
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900">Welcome back</h2>
        <p class="mt-2 text-sm text-gray-500">Sign in to your account to continue managing your CLT layup data.</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <div>
            <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">Email address</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-brand-800 focus:border-brand-800 placeholder-gray-400 transition-colors"
                placeholder="you@company.com" />
            @if ($errors->has('email'))
                <p class="mt-2 text-sm text-red-600">{{ $errors->first('email') }}</p>
            @endif
        </div>

        <div>
            <div class="flex items-center justify-between mb-2">
                <label for="password" class="block text-sm font-semibold text-gray-700">Password</label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-sm font-medium text-brand-800 hover:text-brand-900 transition-colors">Forgot password?</a>
                @endif
            </div>
            <input id="password" type="password" name="password" required autocomplete="current-password"
                class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-brand-800 focus:border-brand-800 placeholder-gray-400 transition-colors"
                placeholder="Enter your password" />
            @if ($errors->has('password'))
                <p class="mt-2 text-sm text-red-600">{{ $errors->first('password') }}</p>
            @endif
        </div>

        <div class="flex items-center">
            <input id="remember_me" type="checkbox" name="remember"
                class="w-4 h-4 rounded border-gray-300 text-brand-800 focus:ring-brand-800" />
            <label for="remember_me" class="ml-2 text-sm text-gray-600">Remember me for 30 days</label>
        </div>

        <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-brand-800 text-white text-sm font-semibold rounded-lg hover:bg-brand-900 transition-colors">
            Sign in
        </button>
    </form>

    <p class="mt-8 text-center text-sm text-gray-500">
        Don't have an account?
        <a href="{{ route('register') }}" class="font-semibold text-brand-800 hover:text-brand-900 transition-colors">Create an account</a>
    </p>
</x-guest-layout>
