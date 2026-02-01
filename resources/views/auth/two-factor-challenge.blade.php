<x-guest-layout>
    <div class="text-center mb-6">
        <h2 class="text-xl font-semibold text-white mb-2">Two-Factor Authentication</h2>
        <p class="text-slate-400 text-sm">Enter the code from your authenticator app</p>
    </div>

    <!-- Generic Error (e.g., session expired) -->
    @if($errors->has('error'))
        <div class="mb-4 bg-red-500/10 border border-red-500/20 rounded-lg p-3">
            <p class="text-sm text-red-400 text-center">{{ $errors->first('error') }}</p>
        </div>
    @endif

    <form method="POST" action="{{ route('two-factor.verify') }}" id="2fa-form">
        @csrf

        <div id="code-input" class="mb-6">
            <label for="code" class="block text-sm font-medium text-slate-300 mb-2">Authentication Code</label>
            <input type="text" id="code" name="code" autofocus
                maxlength="6" pattern="[0-9]{6}" inputmode="numeric" autocomplete="one-time-code"
                placeholder="000000"
                class="block w-full text-center text-2xl tracking-widest font-mono rounded-lg border-0 bg-white/10 text-white placeholder-slate-500 shadow-sm ring-1 ring-inset ring-white/20 focus:ring-2 focus:ring-cyan-400 px-4 py-4">
            @error('code')
                <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div id="recovery-input" class="mb-6 hidden">
            <label for="recovery_code" class="block text-sm font-medium text-slate-300 mb-2">Recovery Code</label>
            <input type="text" id="recovery_code" name="recovery_code"
                placeholder="xxxxx-xxxxx"
                class="block w-full text-center text-lg tracking-wide font-mono rounded-lg border-0 bg-white/10 text-white placeholder-slate-500 shadow-sm ring-1 ring-inset ring-white/20 focus:ring-2 focus:ring-cyan-400 px-4 py-4">
            @error('recovery_code')
                <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-6">
            <button type="submit"
                class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-semibold text-slate-900 bg-cyan-400 hover:bg-cyan-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-400 focus:ring-offset-slate-900 transition">
                Verify
            </button>
        </div>

        <div class="text-center">
            <button type="button" id="toggle-recovery" class="text-sm text-slate-400 hover:text-white transition">
                Use a recovery code instead
            </button>
        </div>
    </form>

    <script>
        document.getElementById('toggle-recovery').addEventListener('click', function() {
            const codeInput = document.getElementById('code-input');
            const recoveryInput = document.getElementById('recovery-input');
            const codeField = document.getElementById('code');
            const recoveryField = document.getElementById('recovery_code');

            if (codeInput.classList.contains('hidden')) {
                codeInput.classList.remove('hidden');
                recoveryInput.classList.add('hidden');
                codeField.focus();
                recoveryField.value = '';
                this.textContent = 'Use a recovery code instead';
            } else {
                codeInput.classList.add('hidden');
                recoveryInput.classList.remove('hidden');
                recoveryField.focus();
                codeField.value = '';
                this.textContent = 'Use authenticator code instead';
            }
        });
    </script>
</x-guest-layout>
