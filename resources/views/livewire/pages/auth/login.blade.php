<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form wire:submit="login" method="post" class="space-y-5">

        <!-- Email -->
        <div>
            <label for="email" class="block text-xs font-bold tracking-widest mb-2" style="color: var(--guest-title);">
                CORREO ELECTRÓNICO
            </label>
            <div class="flex items-center rounded-xl border px-4 py-3 focus-within:ring-2 focus-within:ring-blue-300" style="background-color: var(--guest-input-bg); border-color: var(--guest-input-border);">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-3 flex-shrink-0" style="color: var(--guest-subtitle);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                </svg>
                <input
                    wire:model="form.email"
                    id="email"
                    type="email"
                    name="email"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="nombre@universidad.edu"
                    class="flex-1 bg-transparent border-none outline-none text-sm placeholder-[var(--guest-subtitle)]"
                    style="color: var(--guest-title);"
                />
            </div>
            <x-input-error :messages="$errors->get('form.email')" class="mt-1" />
        </div>

        <!-- Password -->
        <div>
            <div class="flex items-center justify-between mb-2">
                <label for="password" class="text-xs font-bold tracking-widest" style="color: var(--guest-title);">
                    CONTRASEÑA
                </label>
            </div>
            <div class="flex items-center rounded-xl border px-4 py-3 focus-within:ring-2 focus-within:ring-blue-300" style="background-color: var(--guest-input-bg); border-color: var(--guest-input-border);">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-3 flex-shrink-0" style="color: var(--guest-subtitle);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                </svg>
                <input
                    wire:model="form.password"
                    id="password"
                    type="password"
                    name="password"
                    required
                    autocomplete="current-password"
                    placeholder="••••••••"
                    class="flex-1 bg-transparent border-none outline-none text-sm placeholder-[var(--guest-subtitle)]"
                    style="color: var(--guest-title);"
                />
            </div>
            <x-input-error :messages="$errors->get('form.password')" class="mt-1" />
        </div>

        <!-- Submit -->
        <button
            type="submit"
            class="w-full flex items-center justify-center gap-2 py-3.5 rounded-xl text-white font-semibold text-sm tracking-wide transition hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-400"
            style="background-color: #1a237e;"
        >
            Iniciar Sesión
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
            </svg>
        </button>

    </form>

    <!-- Contact admin -->
    <p class="text-center text-sm mt-6" style="color: var(--guest-subtitle);">
        ¿No tienes acceso?
        <a href="mailto:admin@university.edu" class="font-semibold hover:underline" style="color: var(--guest-title);">
            Contacta al Administrador
        </a>
    </p>
</div>
