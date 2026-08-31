{{--
    A password field with a show/hide toggle.

    The eye sits inside the field rather than beside it, so the control keeps
    the same footprint as every other input on the form and nothing reflows
    when it appears.

    Degrades honestly without Alpine: the input carries a real
    `type="password"` attribute (the `:type` binding only takes over once
    Alpine boots), and the "hide" icon is x-cloaked, so a page with no
    JavaScript shows an ordinary masked field with a dead eye rather than a
    plain-text password.

    Usage — every attribute except `name` is passed straight through, so the
    caller keeps control of the styling and the validation attributes:

        <x-password-input name="password" required
            class="pw-field premium-input w-full h-14 px-6 ..." placeholder="Create Password" />

    `pw-field` reserves the room on the right and `pw-toggle` parks the button
    in it; both live in the inline stylesheet in app-layout, so neither depends
    on the Tailwind build being regenerated.
--}}
@props([
    'name' => 'password',
    // Buttons inside a form default to submitting it; this one must never do
    // that, hence type="button" on the toggle below.
    'toggleLabel' => 'password',
])

<div class="relative" x-data="{ show: false }">
    <input
        type="password"
        :type="show ? 'text' : 'password'"
        name="{{ $name }}"
        {{ $attributes }}>

    <button type="button"
            @click="show = !show"
            tabindex="-1"
            aria-label="Show {{ $toggleLabel }}"
            :aria-label="show ? 'Hide {{ $toggleLabel }}' : 'Show {{ $toggleLabel }}'"
            :aria-pressed="show"
            class="pw-toggle absolute w-10 h-10 flex items-center justify-center rounded-xl text-white/40 hover:text-white hover:bg-white/5 transition-colors">
        {{-- Open eye: the password is hidden, tap to reveal. --}}
        <svg x-show="!show" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
        </svg>
        {{-- Struck-through eye: the password is showing, tap to hide. --}}
        <svg x-show="show" x-cloak class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.477 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
        </svg>
    </button>
</div>
