<x-guest-layout>
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900">Create your account</h2>
        <p class="mt-2 text-sm text-gray-500">Get started with CLT Layup Manager to organize your suppliers and layup data.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf

        <div>
            <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">Full name</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-brand-800 focus:border-brand-800 placeholder-gray-400 transition-colors"
                placeholder="Alex Morgan" />
            @if ($errors->has('name'))
                <p class="mt-2 text-sm text-red-600">{{ $errors->first('name') }}</p>
            @endif
        </div>

        <div>
            <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">Email address</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username"
                class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-brand-800 focus:border-brand-800 placeholder-gray-400 transition-colors"
                placeholder="you@company.com" />
            @if ($errors->has('email'))
                <p class="mt-2 text-sm text-red-600">{{ $errors->first('email') }}</p>
            @endif
        </div>

        <div>
            <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">Password</label>
            <input id="password" type="password" name="password" required autocomplete="new-password"
                class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-brand-800 focus:border-brand-800 placeholder-gray-400 transition-colors"
                placeholder="Min. 8 characters" />
            @if ($errors->has('password'))
                <p class="mt-2 text-sm text-red-600">{{ $errors->first('password') }}</p>
            @endif
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-2">Confirm password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-brand-800 focus:border-brand-800 placeholder-gray-400 transition-colors"
                placeholder="Re-enter your password" />
            @if ($errors->has('password_confirmation'))
                <p class="mt-2 text-sm text-red-600">{{ $errors->first('password_confirmation') }}</p>
            @endif
        </div>

        <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-brand-800 text-white text-sm font-semibold rounded-lg hover:bg-brand-900 transition-colors">
            Create account
        </button>
    </form>

    <p class="mt-8 text-center text-sm text-gray-500">
        Already have an account?
        <a href="{{ route('login') }}" class="font-semibold text-brand-800 hover:text-brand-900 transition-colors">Sign in</a>
    </p>
</x-guest-layout>
