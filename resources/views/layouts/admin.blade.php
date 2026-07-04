<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard - POS')</title>
    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts: Plus Jakarta Sans for UI, Inter for body -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Chart.js for beautiful analytics graph -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        darkBg: '#0B1120',
                        sidebarBg: '#111827',
                        cardBg: '#1F2937',
                        accentGreen: '#B4F481',
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        display: ['Plus Jakarta Sans', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        body { 
            background-color: #0B1120; 
            color: #FFFFFF; 
        }
        .sidebar { 
            background-color: #111827; 
        }
        .card { 
            background-color: #1F2937; 
            border: 1px solid #374151; 
        }
        /* Custom scrollbar for sidebar */
        ::-webkit-scrollbar {
            width: 4px;
            height: 4px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: #374151;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #4B5563;
        }
    </style>
</head>
<body class="flex h-screen overflow-hidden text-sm font-sans antialiased">

    <!-- Panggil Sidebar -->
    @include('components.sidebar')

    <!-- Main Content Wrapper -->
    <main class="flex-1 flex flex-col h-full overflow-y-auto p-8 relative">
        <!-- Background Glow Blobs -->
        <div class="absolute top-10 right-10 w-96 h-96 bg-[#B4F481]/5 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute bottom-10 left-10 w-96 h-96 bg-blue-600/5 rounded-full blur-3xl pointer-events-none"></div>

        <!-- Panggil Headbar -->
        @include('components.headbar')

        <!-- Konten Utama Halaman -->
        @yield('content')
    </main>

    <!-- Interactive script for sidebar dropdowns and dynamic clock -->
    <script>
        function toggleDropdown(menuId, iconId) {
            const menu = document.getElementById(menuId);
            const icon = document.getElementById(iconId);
            
            if (menu.classList.contains('hidden')) {
                menu.classList.remove('hidden');
                icon.classList.add('rotate-180');
            } else {
                menu.classList.add('hidden');
                icon.classList.remove('rotate-180');
            }
        }

        // Live Clock Indonesian Localized
        function updateClock() {
            const now = new Date();
            const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            const months = [
                'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 
                'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
            ];
            
            const dayName = days[now.getDay()];
            const dayNum = now.getDate();
            const monthName = months[now.getMonth()];
            const year = now.getFullYear();
            
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            
            const dateStr = `${dayName}, ${dayNum} ${monthName} ${year}`;
            const timeStr = `${hours}:${minutes}:${seconds}`;
            
            const clockEl = document.getElementById('live-clock');
            if (clockEl) {
                clockEl.textContent = dateStr;
            }
        }
        setInterval(updateClock, 1000);
        updateClock();
    </script>
</body>
</html>
