<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk | POS Retail System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#0b1120] text-white min-h-screen flex flex-col items-center justify-center p-4 relative overflow-hidden font-sans">
    
    <!-- Background Glow Blobs for Premium Aesthetic -->
    <div class="absolute top-1/4 left-1/4 -translate-x-1/2 -translate-y-1/2 w-80 h-80 bg-blue-600/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-1/4 right-1/4 translate-x-1/2 translate-y-1/2 w-96 h-96 bg-[#b4f45b]/5 rounded-full blur-3xl pointer-events-none"></div>

    <!-- POS Logo and System Title -->
    <div class="flex flex-col items-center mb-8 z-10 select-none">
        <div class="flex items-center justify-center w-16 h-16 bg-[#b4f45b] rounded-2xl shadow-[0_0_25px_rgba(180,244,91,0.25)] transform transition hover:scale-105 duration-300">
            <span class="text-black font-black text-2xl tracking-tighter">POS</span>
        </div>
        <span class="mt-3 text-[11px] font-bold tracking-[0.25em] text-gray-400 hover:text-[#b4f45b] transition-colors duration-200 uppercase">Retail System</span>
    </div>

    <!-- Main Card Container -->
    <div class="w-full max-w-md bg-[#131b2f]/95 backdrop-blur-md rounded-2xl border border-gray-800/80 p-6 sm:p-8 shadow-2xl z-10">
        
        <!-- Demo Accounts Section -->
        <div class="bg-[#0b1120]/80 border border-gray-850 p-4 rounded-xl mb-6 relative overflow-hidden group select-none">
            <!-- Shimmer effect -->
            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/5 to-transparent translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-1000 ease-out"></div>
            
            <div class="flex justify-between items-center mb-2.5">
                <span class="text-[10px] font-extrabold tracking-wider text-[#b4f45b] uppercase">Akun Demo (Role)</span>
                <span class="px-2 py-0.5 text-[9px] font-bold bg-[#b4f45b]/10 text-[#b4f45b] rounded-full uppercase">[Admin]</span>
            </div>
            <div class="space-y-1.5 text-xs">
                <div class="flex justify-between items-center text-gray-400">
                    <span>Email:</span>
                    <span class="text-white font-mono select-all font-semibold">admin@pos.com</span>
                </div>
                <div class="flex justify-between items-center text-gray-400">
                    <span>Kata Sandi:</span>
                    <span class="text-white font-mono select-all font-semibold">password</span>
                </div>
            </div>
        </div>

        <!-- Error Alert Message -->
        @if ($errors->any())
            <div class="bg-red-500/10 border border-red-500/25 text-red-400 p-4 rounded-xl mb-6 text-sm flex items-start space-x-2.5">
                <svg class="w-5 h-5 flex-shrink-0 mt-0.5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <!-- Login Form -->
        <form action="{{ url('/auth/login') }}" method="POST" class="space-y-5">
            @csrf
            
            <!-- Email Input -->
            <div>
                <label for="email" class="block text-xs font-bold tracking-wider text-gray-400 uppercase mb-2">Email Pengguna</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206"/>
                        </svg>
                    </span>
                    <input type="email" name="email" id="email" 
                        value="{{ old('email') }}" 
                        class="w-full bg-[#0b1120] text-white border border-gray-800 rounded-xl py-3.5 pl-11 pr-4 text-sm placeholder-gray-600 focus:outline-none focus:border-[#b4f45b] focus:ring-1 focus:ring-[#b4f45b] transition-all duration-200" 
                        placeholder="nama@email.com" required autocomplete="email" autofocus>
                </div>
            </div>

            <!-- Password Input -->
            <div>
                <label for="password" class="block text-xs font-bold tracking-wider text-gray-400 uppercase mb-2">Kata Sandi (Password)</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </span>
                    <input type="password" name="password" id="password" 
                        class="w-full bg-[#0b1120] text-white border border-gray-800 rounded-xl py-3.5 pl-11 pr-4 text-sm placeholder-gray-600 focus:outline-none focus:border-[#b4f45b] focus:ring-1 focus:ring-[#b4f45b] transition-all duration-200" 
                        placeholder="••••••••" required autocomplete="current-password">
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" 
                class="w-full mt-2 bg-[#b4f45b] hover:bg-[#a3e24a] text-black font-extrabold text-xs tracking-wider uppercase py-4 rounded-xl shadow-[0_4px_20px_rgba(180,244,91,0.15)] hover:shadow-[0_4px_25px_rgba(180,244,91,0.3)] hover:scale-[1.01] active:scale-[0.99] transform transition-all duration-200 cursor-pointer">
                Masuk Ke Sistem
            </button>
        </form>
    </div>

    <!-- Footer Copyright -->
    <div class="mt-8 text-center text-xs text-gray-600 z-10 select-none">
        &copy; {{ date('Y') }} POS Retail System. All rights reserved.
    </div>

</body>
</html>
