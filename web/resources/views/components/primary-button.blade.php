<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-accent-strong border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#9E0E19] focus:bg-[#9E0E19] active:bg-[#7A0B13] focus:outline-none focus:ring-2 focus:ring-accent focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
