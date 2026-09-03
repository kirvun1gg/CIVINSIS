<x-guest-layout>
    <x-auth-card>
        <x-slot name="logo">
            <a href="/">
                <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
            </a>
        </x-slot>

        <div class="mb-4 text-sm text-gray-600">
            {{ __('Hemos enviado un código de verificación a tu correo electrónico de Google. Por favor, ingrésalo a continuación para continuar.') }}
        </div>

        <!-- Validation Errors -->
        <x-auth-validation-errors class="mb-4" :errors="$errors" />

        <form method="POST" action="{{ route('google.verify') }}">
            @csrf

            <!-- Email (Oculto) -->
            <input type="hidden" name="email" value="{{ $email }}">

            <!-- Código -->
            <div>
                <x-label for="code" value="{{ __('Código de Verificación') }}" />

                <x-input id="code" class="block mt-1 w-full text-center tracking-widest text-lg" type="text" name="code" required autofocus autocomplete="off" />
            </div>

            <div class="flex items-center justify-end mt-4">
                <a class="underline text-sm text-gray-600 hover:text-gray-900" href="{{ route('login') }}">
                    {{ __('Volver al Login') }}
                </a>

                <x-button class="ml-4">
                    {{ __('Verificar') }}
                </x-button>
            </div>
        </form>
    </x-auth-card>
</x-guest-layout>

