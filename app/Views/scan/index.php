<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scan Jimpitan</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        dark: '#0f172a',
                    }
                }
            }
        }
    </script>
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <!-- Html5Qrcode -->
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        body { font-family: 'Inter', sans-serif; }
        #reader { width: 100%; border-radius: 12px; overflow: hidden; background: #000; }
        #reader video { object-fit: cover; border-radius: 12px; }
        
        @keyframes heartbeat {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); box-shadow: 0 0 15px rgba(99, 102, 241, 0.5); }
        }
        @keyframes heartbeat {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); box-shadow: 0 0 15px rgba(99, 102, 241, 0.5); }
        }
        .animate-heartbeat {
            animation: heartbeat 2s infinite ease-in-out;
        }
        .flash-on {
            background: linear-gradient(135deg, #facc15 0%, #ca8a04 100%);
            box-shadow: 0 0 20px rgba(250, 204, 21, 0.6);
            border: 2px solid #fef08a;
            transform: scale(1.1);
        }
        .flash-off {
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .flash-off {
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
    </style>
</head>
<body class="min-h-screen flex flex-col bg-slate-50 text-slate-900 dark:bg-slate-900 dark:text-slate-100 transition-colors duration-200">

    <!-- Header -->
    <div class="bg-indigo-600 text-white p-4 shadow-lg sticky top-0 z-50">
        <div class="max-w-md mx-auto flex items-center justify-between">
            <a href="<?= base_url('/') ?>" class="p-2 -ml-2 hover:bg-white/10 rounded-full transition-colors">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div class="text-center">
                <h1 class="text-lg font-bold"><?= $title ?></h1>
                <p id="headerClock" class="text-[10px] text-indigo-100 font-mono leading-tight">...</p>
            </div>
            <button id="themeToggle" class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center hover:bg-white/20 transition-colors">
                <i class="fas fa-moon dark:hidden"></i>
                <i class="fas fa-sun hidden dark:block text-amber-300"></i>
            </button>
        </div>
    </div>

    <!-- Main Content -->
    <div class="flex-1 p-4 max-w-md mx-auto w-full flex flex-col gap-4">
        
        <!-- Scan Stats Card -->
        <div class="bg-white dark:bg-slate-800 p-4 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700 flex items-center justify-between transition-colors relative z-10">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                    <i class="fas fa-qrcode text-lg"></i>
                </div>
                <div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 uppercase font-bold tracking-wide">Total Scan Hari Ini</p>
                    <p id="totalScanDisplay" class="text-lg font-bold text-slate-800 dark:text-white">
                        <?= $scanCount ?? 0 ?> KK - Rp <?= number_format($totalNominal ?? 0, 0, ',', '.') ?>
                    </p>
                </div>
            </div>
            <button onclick="openDetailModal()" class="px-4 py-2 bg-indigo-600 text-white text-sm font-bold rounded-lg hover:bg-indigo-700 transition-all cursor-pointer active:scale-95 animate-heartbeat shadow-lg shadow-indigo-500/30 border border-indigo-400/20">
                <i class="fas fa-list-ul mr-1"></i> Detail
            </button>
        </div>

        <!-- Scanner Area -->
        <div class="bg-white dark:bg-slate-800 p-2 rounded-2xl shadow-md border border-slate-100 dark:border-slate-700 transition-colors relative group">
            <div id="reader" class="w-full text-center"></div>
            
            <!-- Flash Toggle Button -->
            <button id="flashToggle" onclick="toggleFlash()" class="absolute top-4 right-4 w-10 h-10 rounded-full flex items-center justify-center transition-all duration-300 hidden z-20 flash-off animate-heartbeat group/btn">
                <i class="fas fa-bolt text-lg text-yellow-400 group-hover/btn:animate-pulse"></i>
            </button>

            <p class="text-center text-xs text-slate-400 mt-2 pb-1">Arahkan kamera ke QR Code Warga</p>
        </div>
    </div>

    <!-- Detail Modal -->
    <div id="detailModal" class="fixed inset-0 z-[100] flex items-center justify-center invisible opacity-0 transition-opacity duration-300">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="closeDetailModal()"></div>
        <div class="bg-white dark:bg-slate-900 w-[95%] max-w-md rounded-2xl shadow-2xl transform scale-95 opacity-0 transition-all duration-300 pointer-events-auto h-[80vh] flex flex-col">
            <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-indigo-600 text-white rounded-t-2xl">
                <div><h3 class="font-bold text-lg">Daftar Scan Hari Ini</h3><p class="text-xs text-indigo-200">Realtime Update</p></div>
                <div>
                    <button onclick="refreshDetails()" class="w-8 h-8 rounded-full bg-white/20 hover:bg-white/30 inline-flex items-center justify-center transition-colors"><i class="fas fa-sync-alt text-sm"></i></button>
                    <button onclick="closeDetailModal()" class="w-8 h-8 rounded-full bg-white/20 hover:bg-white/30 inline-flex items-center justify-center transition-colors ml-2"><i class="fas fa-times"></i></button>
                </div>
            </div>
            <div id="detailList" class="flex-1 overflow-y-auto p-4 space-y-3 custom-scrollbar"></div>
            <div class="p-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50">
                <div class="flex justify-between items-center text-sm font-bold text-slate-600 dark:text-slate-300"><span>Total Data:</span><span id="detailCount" class="text-indigo-600 dark:text-indigo-400">0</span></div>
            </div>
        </div>
    </div>

    <!-- Audio Effect (Handled by JS) --> 
    <!-- Create a simple beep using JS if file incorrect, but prefer valid asset path. For now assume system sound or JS beep -->

    <script>
        // --- Theme Logic ---
        const themeToggle = document.getElementById('themeToggle');
        const html = document.documentElement;

        if (themeToggle) {
            themeToggle.onclick = () => {
                html.classList.toggle('dark');
                const isDark = html.classList.contains('dark');
                localStorage.setItem('theme', isDark ? 'dark' : 'light');
            };
        }
        // --- End Theme Logic ---

        const beep = new Audio("data:audio/mp3;base64,//PkZAAhGd0oAK5gAJJYqlwXWBgAWEQkFsEADmK3opv8iYYJCpiUZmNxeYRARkRAGtm8bycxrAZGg3Oea0x5CnG8kcYkEZl07mezeZZHph8CusgnAIS7bzugoAio5pbs3ONQGOK3orsHkjWGuP3NNYYhJmBoS1L14GMoCOj/A8ofyMWP1SRikuu25b9zDD2vxfKkpLGNPGLErctnbO3fn5Q5bv26kollJh/7r09vWGG6Sksc//wpI3G5fk+jDFSOo6aEhIhlk5Xp8//v/+sOfnSWMMMMMPwz1SWLsNuXF5hh7E3ft6wwww7//+efdZ59/WH//7zz/DDDdJSWMMP///888/1h//nnnn2pGHLcuH8n0YYxB+I+sIsR3JyOPFITb4YUlJh//hTxuH776KYJiNotMwCAwFLJ2G3fh+33+IEAEcAEE4GLB8HAQOSgPh/+D/+D75dH/lAQgg7//w/B/////5QEAQcqAoACFHIQMAZmSIJQJgKPZ/QUNS7TBcDAsFMbAQENkQ6DpRG19JmBIXmPVcmBYTmQhrnvA+mGAEGF4EH4gEGBIE+fPBeYEgQWAJLIgUGCgZCg7fUSLBuGCIIrvL6GBZdFY7goBoJaoguYaBk/3yVkBgWGSnMCUtKA//PkZHgoQhc+q870ACzcFrY/mZEDQ5HhHavIJI1VDASF6//3RkCQCBCsMH01OCAFRiz5QqlDAfaU4FL9I0swKBMSAFNX/+mFAKU/Ld5zim8gvdmf//aRd//p71+8xem/7oyABYAcMAWp9K8S9KW/ekmQNAx1u/+pPr/+4FATUQv//wBf//dMtQ21H9yho8v/+Nf5jgW5ou67Vnd//zbvb5eGgTbz/u/e//pmyt7e/85ivjqaxQRIWs55/Oyz/////uT3/rb4SjLL/m7P///9+/9Nev3oi+C9ael+/T//3QodQAAUeGgr5RJnZazeiTXk4MYR/tXTMCFKi7ANggwGhzLS5KvAF9NEgswTpXFIlQ3lgtEIRcfgyAOMzIAGKkPLhQRPHB0E8gxeE8H/iplxbJmY321ll/f9bDsniZLAgIRc2MkZRoqV9nQSZJJ0FqT1/+XCwbHk1qQVdOITGSaCKjv0kkktHS0ez//eUBkCRNmZd+gSZ7/1//8+eL5wtjkDgLZ7L//MmUswLgm8codxq6U/VTCRBGMEECZN9BV1aGgVufOiddIxf4IAIMCcAEwcAqTDgMJPhLj4+ao5TSiMJMgoNswUwojB9BkMF8DILANmA0AqRAIvg6y+QQAKChgS//PkZE0m6gsgAO90ACgbqjwB3aAAChh+SBmebR/3UpoClRkuOBiOCjO0xUxaKNUdD7pM6dMwAAkwaDkxYHc3eGUwPAhW9MVMF+a1Nudv8s/lGvjBjuExgQBKuaKNRr/+j//ofdIEgAGCcYfAeYAgQmM63//373/9y7SspLewDfu////0f/Q/Go06ZhyHBhCChgAAKSNF//9BRUFF9B9BGlcs5jVH//9BQ/RfR/RM5QSugmM6rovl//9B//9BRxqNUf/////9F9F/0MZQSur9BG//6L6Cj//+gjUa+ijNF9F///0X0Pr6Vy6rOYzQUfxn///+h98Y3GvoP////ovoTAYXTBYBwwDYwzqhbs05wXKcowDAwwBAEwAAEsAAYKgoWAUMqKYNbxH/ywCgWAdToLAP6nQmgmoYpE0iV4GV7gyNx+/4lQC4YDDqAO6XA3ZYBgcJqRYiuW/4lYCwcGBgxT//iVALqAYX//8RmMj//j+CAGCIwHTEL//koRI9//OCzhKYXUl4unj3/5aIt//ywMkMj/////iVhikMUhikMUoIkCmYAwBiBIsmXTgxUkHRtyHITHWgtMwEAEDAjBRMD4P4x24XDJRC/MFECIwEABwEAcGAxMQZA5hEIoEzA4ZM//PkZEAmQf0yAXuPbCFrniAA3aiQRhkrCJhgfGRh8AiKZ1WoCm52yDGmwiZsSBmwIGGR+ZHH40RzCAsMWCIuiYYERhgDIETA4HUZLIFgDmBwMpAeBwMBgkDExy6an1oJiQctCDnLg9aCVgjA4CDZYAwODaHAtY/qVaV7ZF2NmL4OB6X8y1IZAUEyf//5P0waHNM0xgAMAC+CvACzSNA0E0mTT7xV4eafzzCYz336azf0viJl/OpHr/9/3k0r/v/3nf+T////9M9MGgaIasewahMmgmjTTf///////m/7yaX9/5JmOd5NJN+/8k3eTSzyM/////Prk7Po+Sdk6AWCcgLZ8BKD4Po/8WAosmwBRctOgUWk/y0nps/5lhZgam6qAyqv//gZPlNf/8DHjwN26A8mUIjgMcOBg4GweAMvg2DAbBkGwYF1gw8MNCI8DHjwMeOBg8IjgYO/D9BAAXKGKxc4/CAAGRDhEhH7///8hCFj9IWQgDEULSxc/5C/ww3//////////////FZFXd/QFrdyRDH50MRgcxoIwwYmBxEYRCC0wAB0IXJWr8kk7+tlQCGoV6ahGACBrSpI/y7Wmv4lUlbJC1RapAiYMCpiIDmBxgcTW5lsbA0GmFAqAgyo//PkZFMnBhlI/3NTnCdT7mlQ29tkkAQxfppKAVpzSV2rsQDNPLIiIYc1SDhy70AoCMqJGHDF+lDS1snL6tNL7F+kPTJDzERDtpTPkiIkXlAQVmqPSAZ+0eVTQaqVqzAlTPuWtZt////GpO0l/pKu1pqVQCHGHDnSGnNKSRAMu6SrtabJF3QetJynLcmDnKWsgSWt///wd7lQfBjlQa5LGR8qpIspKiipKpKixktl+rRqSmReMyAjwF/g+UliZNWSSospGk9F0f9lLZJ1opOv+v58vl4+XZdLpwvF46OaeHNHNOCAwnIB3QblAoIZ0UEfLx44dOF0vF6XT8CABCECf9qzqfQRSS/G4xRf/tmbMZrQmgjBYC2ztmbK2ddjZmyLvXeu9s7Zl2mFDBjBMaCniMKAIyYyFrsL7lYWX0QJeWTbO2Zy4Mg6DhgMRXVWcmDYlUe5PW+VjlZ3NFygVJjYRiL8yPfeVaaK3SlFv/kWMIMORwMQYYYQYT+rs7oO3///////8RwLQC0f/////////+WY9Cse5UVKQICBpEmsK3SJ3XCb5lTleu8voaKJoTH76YEEBXGB5hDZiHiUWYskCnGBuAKpgNYAgYB4AIoBgcAQ+okDQBBRgsACJgEwAiok//PkZEglvgkiWWf1SqYb8kQA7Wa8WABAwBEARBgEEYGOBXmUuBzhgHgL6VgHgMAJwcAQqJg4AhQDegG9RNALh5QDAgBj1XgeTnAMCABwgDzgWAouxsiIhbyVhjiXFAjmkgHlDzAYnTAG3QgFkIWRf5enpdLgQBIM0dOl7ODq/F0LsLyAyCrAMMgoQVCxwYvz5w/Lxw+OYBIFjFL8vF7LkvTnjoLx/50/5w9j8fPHZ/PZ06XS8LlLoXxAIFYGHAKGNSFHULmOl06XZ3zw6ROv//xBX//jFEFRiEiU3Ukya0SiRpWKTjeGSAwIEQBQKIwFxEVJEolIixbLSM4KA8uSzlsvyb1OfZy+LOPLACFYC/5YmEGSLwMCAUDAgFAwIBQYBP4RQQR4QMkYGg0FwMNBsDDQbhENwiG/wYg/xFYXChcN8DAgFCImBgEgwC//8b38u5wvl0lC+OaBsqBtIJxLh+cP/+N4b//////jc/zhwul08XiHBboHTC5QgiOcXjp48Xf+Q0u////////4RIDCwMpAYWowIBANqnKKrOY1R0rZGVQeEANmAYAEWAAzA2A2MGsCswfgYDHhB1PGLVI+IkaTI/EwMMgJ8wiQVDA2ArMA0CowFgITAXACVhclTgrA//PkZEwmugEYCXv1hiMT7jAA76rkDRUCoBpgLALGAQgIJgTwJMaDWJDmBphaBgFYBCioWAAODYPgyDYPciD4N9TlTgIAGjAYAPMIHgAgCXMBAAIQgAGEQACDQAB8aeIRW/Fb0VTQIUOkAwuagFTsAYFxcw/8fvxzcAkNCbJLkvJaS5Cfj8HQgYCT4IkqHQAiAxCfO5ePDdL5EwbyCly+REiJKHj5fkOOnjs+cHUIufPnC9Oj/OT8unJwdA/nR/Pl44cFoPHS6Xi+cni6BgQSjEHccOHS6Xp44XC9Lh48Xh1F/j//5C/j/x+kLxcouYOjIWP5C//j8AkQAKBshPIQsAsmwmwip/vizt8FGkVkV/LTmGIYGGIlgQSzehzzx4fwMsxWGBguC/lp/AoLegUgWmz/psFpE2DJrQJMGULIDBKlYC6bAXX/8LrhEYgbMJQNg/JWXC6cl47PcAUYADJeF1//4/j8P5C//8Lrg2DP//FUKv/////iK///C64Ng3///////////G6NyN85h+MXFk2ECAXB1oiAAasICErDjOg4zs7LFkdnZnZWRgwISiYMAOVGYD+sRlM4y6YfeEoGDAAwJWBZ+YC+AvGAvAL5gL4C9///+YC+AvmBZAWRgwIS//PkZFUlnfkMAG/2SB8izhwA3aqQicCAJfmJeCxpYAsv8IgOAwHAPBgDwMB4DgiA/8IhfCKSgML7vYMC/AOAkCIAgc8R6RUR2P44hPojsXJgYLQ6gadwWAYLAW//H4hBcgfsACAcAILMXIQhCj8QgWBIQVRaIsKwQoj8cYXNgOAoBgDAyFzYXMxyywOQWCLj/yFIUhAtIBAAcQCH+P/xc5CkL8NXfisiq8VnFYCIARWF4qxWYauis8VYrIrIrADwIAwEPirFYDVvisirFYFXDVlX//1//4MBbhh/C62F1/DDhdYDAuDAGweF1guudEXf6AX/9si7ECJZAAGhkTGZERmREeEW8AfDiKAwiuDAEAwBAGAkF4MAQDAX4RCd4GJkTIHNxAoG3ATPCIiCIi4REf8DXcn4ugvIYogpxdYgpgwRgblEX//DyhZCHmDyf/xdiC4GCgyFjv//EFRBcXQu/////+EQT//8PJVECMC2ZsjjPe9kek7SosXkHRIQoCLZlLQQHcYiwdBrHoEG6GHsYbQTZghgKGAoAqEAVGBYAqVgElYBBYAI//MBQBTywBEYCoCpgjCLG8O98ZAYghWAr/gYAGYGAADCIBCIAwYAPAwqFQOCxYDCgUxvCTEyXCiO//PkRHYawfcaBWvVSDOD7jgIz6qWQkXyoRYuwwwC5RAwqMBNAut/Ls7IZJ0bwnEVR08fn88cn5cIaWQ1QNI8fOncu/45wcIc0lclf/y1+WS1/losFuWyx/8fg6Qfv////8s5a//yKiNCXln+W8slkswvAipYQD73zfF33oazGFPummQ5yHEzpAvKZ0xgyg+mEICkauYopwBinGF4CKDADjAOAPMA8CQLAHlYAhWAKWABf/zAOAO8sANmAeAeYHQQhuPncGLWKEVgH/4EAKLS+gWgV/pseBg8HgdDQgGDgdjFLhMFwZoqpF8qEEE6SwA8dgMJoVRa/l2dkNkTIQmTx08fn88cn5cJklAGgSLw8fOncu/5FhWCKlnLP/5a/LJa/y0WC3LZY/+Pw6h+/////yzlr//IqRQlpZ/lvLJZLMlCKlg7iAgJq7lQp96eWKhCAxCAZKos+K4FhlTkw6w8zFRzbNDcjYwmgPTCPCmMDcDYwFAFTAiAiAwApYAUAgCv/5gWgb+owYFgMxhHgbmxFh+YewHpg6AblYFhYAtBgP8Ig78DCoUA7YqQMKBTFdIsK+XB2jgGgQw8LaRWGGCJlBaiQut/LfLRaHIAQDgbBBFC15Y5aLMsxcwWtCLB+Eih//PkRJ0a3fcWAGfVSDBj7jgAz6qQZLcsZcPZcOwFwGRQipPHDp/L8u5c///4av//4/AwAiVD+P3/+P3yF////4uQXKQpCf/yFH8MhCABCIVl1YPfxwHcc9uktLqKruaYLA9iWMSsDwAC4mG3xGYjJFBiiAzmEqBAYDQDZgCgCIBwwB8sAChYAT/8wFQGvKwPRICkwlRGTWudlMcgL8wRgGisBQsAKgwA+EQB+BgQCgcqNAGBQLlwvF5AkyJFYjWNx0RKgiOwMDByJp/P88eIYI7KZcPec547Oxzgt+IUdMuHT85oNoLifB0l02Ut9Ojof//8XP//yw43SyWP/8sfLf////IoRUtlr/+WyyQMZAtKTEFNRTMuMTAwqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqYMQWAVU95y5XflbWEoom5JaswAB0sAQYOg+YXAQYcmybae+cZAGYEBaYEAoWAUBkAYRCIBEAYEGCDBisQMJgMQNCNGgMDwLgFBNAYCAEgwFsDAQBWDAEfh54RA8CITYmgeeLYSBWHNEoniIls1L5D8IgLAkGUXX+iclcqmg5xExSJgRYyWq/nOMABoAAthw7//HcJYf////Pf509OT1dV9SaikRY//PkRKUWxfMgWHZWYi6j5kAUz6iQYZWTX2/ostX/////HP//+KyYkADqDQmruNS3qWCGLslcodGDgywQIfzAuAIMHEQMzoF/TTgCXCAEDAIAVLACpWAQYAIBHlgAEsAAlYAPlYABWAB5YAc8wDAWjIxaWMMEC4wDQLTAIAJU4gYgpBgj8PPCK4Dt+RNQ88W4kCsOaIWPERLZqXyH4RFgTQi6/0jlJA1J8EgJDycKhgpdvOcckBIUnDh3/+QwTmS////57/OnpyerqvqTUUhYBhlZNfb+iy1f////8c///4rNTEFNRTMuMTAwVVVVVVVVVVVVIIAvGmvBkUlrqOFcpXOEYBrwLAHGAIAGB4Ag0djDoFjY7gjqpCTJ0DjGQAQMHKOwGAZAseAECAADgCLAAlgDiwDnlgHAYCCBQZTKSXBUZMCEAAwHAO0A4GBAgaEABjwAMAQMcAwYAhdYLrAClwOCi4hUeyiXC+OId5fcpkGIUhAQHQLaQ/aLk/OZ/HKHCLIJwu546X/LeRcd4BxsP1G8QYtkoWP/H4coYks///+Wv+W//8skqMgMl//yyWJ7zk/P8uednueLkunPJwvFr/LPlgtkVHGWxNiEyDIGisScK43RoyU6qxYoZCnUhECm//PkRO0aqfccWHfUgDjr7jQAx6qQYI4ixl8SAGsIKSYF4Nxg7ACGBOCIYDAABgYgAFgAEwBQGDAAAFMAQCgsACFgBQsAT+WAJzAoCCMEQHcwXoSSsCkwYgBTAnBGMCkCkDAgEAFAoGFQKDALAwoBcGAWDAADAABgEOgabQHFzFcxQTGgV03MyeG8N8L0ABKkOQiyvzmfxzwTAosJEC7njpf8t5FxQQYUEKHxwFsfyx/43BBAl5Z///8tf8t//5ZH4miY//5ZLE95yfn+XPOz3PFyXTnkQLxa/yz5YLZFRky2TEFNRTMuMTAwqqqqqqqqGAQVhxWHFYcECqnAVCxCImAAJkIAWC0rLSw6+WF89hfLAJ+YJ8H8GNYuOxgbIaCYKqAvlYGwYGyAvlYC+YC+AvFgBe8rAXv/zAsgLIsAWRWBZFgE/LAJ8YJ+2NGFNgn5WCf+VgnwGF8L4RC8EQvwYF7+EQvgaSgvgwL+AQB+DcIYIFAgVAOEQDjdG8N0CgEsIgsBhUAiCz/xWA1fFZDVgavFUGrhWRWBVRWBVBhoYYMNg2DwbB4AoFwMC4MQBALADAvhdaDYM4rGKxFYDVwrAMAiDAAxWMNW8VXFZFWKxir4rEVgVXis4auFYirxVw1a//PkZPAkSfUKpG/2SCZD6hwA1ap0Gr4qxWfDVoGAAAIGBEAIasFXDVwqvxWRWeKqKx/////wYA6F1/4YfDDBh8MMAIBYGAXPvGLBTzAADAgf8sAfMAAM6BM5ZwiM+BlvLcDMKAa/C3cGBvAw3hvCIbgYG8Ihv4RGcDBnAYzxnwiv0D9Gv0GFvBhb8Im4DN5uCJvgw3hE3//DFYlYmvxKwxQGKwiAAYAAMOpMDDocAwCAf/8TQBcLBioSv//AwCAQMdJIDAIcBgA///////////wiFAYFP///////////BgAqTEFNRTMuMTAwqqqqqqqqqqqqqqqqqqqqIIFirLYPoPf5YFezF1glSBwAFgDgcDxWBxYC0x0QY8HX44DHAwJEEwtEH/LAbKMFgD/LAHf5YA8sC15WBaWAjzAbYvMMoHQrAs/0Ck2S0oEAaTZLSeWnau1ZqzVRCBAYEICLV1Teky/LN43RQfR0VHGXKkkHtKAQOrT4G/4hJZPBn3rn3ojdiD1urKrn3v+Sf7+wc2WTwbdlbRB4AtVRlV13///ywryse4jyot8tj3//Kyv/yqP8s5Z+WlRUFaG2F9I5FlRX/8q+Wf+V//lfKh7lR+X8+d589OTgXoOp44fOnzqCC8ik//PkROccXgEcWHfNjDPT6jyo56ER5PQe/sYhyQl8V2AALlgEGCRaVggsCMzE5DzozOAgUy8NjEY2/1GysWlgE+WAR/lgElgP+VgRlgRgxgVaTDtBiKwIv9RJRlAKDAFVGUAnoB2ztmbM2URgMGAyAW2dd3rBRV56e9J796/Sv9QycEgJAoE1hThf7q0FFJPZVjBZiH6iNSkq2dy6W1HywPwZBAHBayLFUfid/PHc6XyUOH/Py//+dO/+cjgnue/PnDhMjkDTLRYnDv/znz3/nf/zvOF849PdfdqqhmCgw23VTEFNJgBfbOzmicuBFKINMAYAArABBoA3psgQFowLQLDDBAsMgxY0xAEErA8w7AkwuCcwjCMwPDsrA4sAeYHAd/mBAWFgEvMCQJMmAtMbz2OhsoM+k1Kx1KwtKwtAxw8Ij4MBgY9HwbB4YcPLCyIALUWg88Z+NIuE4Xy6O4tx1nROQy4BRMZ8d4kpdOTxdzh6cHQeDykAFEOlzOHMlMhCVErWFF4zY8idCW5L/8W7//PnTx0/z8uc75wfj87z/Pzs4dHSHyC3y7Onfzh/njnOZdnj87nD3/OT2cy+XuXcuc5nJdHcHny6cO55ACQwEPg+NPm5MUlTirnaskj6nAVC//PkRPwc2gUaAHu0Vjg0Cjyw7mUwwwaBsxPBsxGykyAIErAQwmAEeDgwVBUwECYrAUsAIYCgL/mAANlgLfMAQBMbgaMjQUMUfgNNgYKxSKwaKwaMUQsCeV0mIr/+ioo0p36YxmoFYan/pvpKSBqelikHfE7q5WSmYG3aLwPS3PvUv/QUX0DoUT4AJFZ1DGP+goMlMhSVEqF0BPg48QmH2S3Jf/h+//+fOnjp/n5c53zhKH53n+fnZw6QwSoacuzp384f545zmXZ4/O5w9/zk9nMvl7l3LnOZyXSJFrLpw7nlaq1f3+oadqsmc4wCEmqphFYEMTj8xaWCwgTf5gOzWw9+7StHlxTFYUMHBZRIKgksCBAosBrysNlZRMWFIwALTCBAAMFMFE0dyojCjH2MCwC0wLAGywA0BhA4GEdBEfA0o4GBAMIPjJhYUN4A0KMYBmkgeWKAFLksSpLDqF0dJQUsOYMxgGkQM2EFARvB5o3vJcl8XQpcQVGbJWMXy2WiKEVloio/BECBgQpLFEgpaljyz5KkKMyRw5stcs/LH5ZloskWlv5YlspyW//i5A/YWWQv////////kWLJZ8teWCzHLIqQR/ltRtTn8pLGFG4PCwBGBYFqNl2CsDzDoJww//PkRP8c2gMaAHPUgDnMBiwA7qcogiwGBlGeZtAZR3QJL5mAhAhgHmIYTFgK0KSwCxYAQsBb5WFhWBJiAOpjgIJhgOBjoBJlpl5pGLZiAIJiAFhYC0x9wx9IsG/BporHmPN+Y4COgWrsiDgJlQsl9U5eyD4Mg9XVxTuDYDctruJvEBQ1fFWS0VfkIQmcHUJALKH6f8lyWHMHNksOaLoPIA3ARcNjD3yWkp5K+Px4dQnUXNJbkr8lPyVksSo50l/kpJcUrIX/+KAGTEERv////////+OcSpK+S3koSsiQ5ozQxPkuN1dCwWlZamyWnQKLA0WBoxsbK0AsIJXQla/5Y2DAXwNgwc4FUMO9QuDIjQNgwF4BeMDYAXzAXwF8rAXjAXgF8wF8DYMBfA2fMBfA2PMBfA2DAXgF4sAbBWAvGBsgbBhJYd4aB8LUmKWCWZgqoGyWANgwNkDYCJegwvgZfL8DLxewiLQiLAYLAYLAMWHQDX0HgwWwuuF1wbBwNg+GHDDBdYAYWBh8Ig4DBy9A0iDgMHA4GA7/C6wNgzhhgMLBYAYXA2DwbB/wYBAiBIMAsIgSDAKBgUCgZodwGJwKDAKEQKDAKDALwiBf8DB47BgP////////hECBEChETwMC//PkZPwiTfkIAG/1SC8i1igA16iQgX//gwCf//////8Lr8LrYYYMOF1gusDp3+u32zLsQILv/zRIiwjNEjNHjME8E8wTwTjEnQdM7ESYxJgTisE8rBPLAERWBEVgRlYEZgRgRFgE7/KwTzBOBOKwTzBOBPMLoLs1YEiSwF2YJwJxYBOME8E4GTwZPwZP4GIXcIiAjVCIgGCQYJEF4NlDFEFIugvPGLDzhF6AdOAOQB5oeX4xIxIguMQYoguILRdRdRdSVkpJXJUNXgkKHPJYVRKEuS5Lxz/4ecLIg8/////////F0IKxdxRAWARzAVAU8wBwB0xCsAcMAODADDAGAGKwFSsBUsAKFgM/ysM/ysW40iavTejL8MW4W8sC3FgW8rBvLANxYBvMG4G7//ywLd5YFuMvwW8y/RbzxJ9iLBfhi3C3lYt3lecV555+d55+d//5Ybytv/z+/r/8wABKwAx0dMBHCwA+VgPmAAHlYCYCAGAAJncmWAErHDHAAwEAMBACwAlgB8rACwAFYB5WA/5WAmAABYHDOx0sABgICVgJWAeVgJgID////5gAAY6OlcmWAAxwBMBAP/ysB//wMIIGEAH8H+DA////9XDFUSoSrE1EriaiaAMNDFQmoYoi//PkZPgi9fUIA3tzmisKzjAAz2iQaRKhNAxQJqJqJqGKBKwxVErE1Er///V/////gYOAYQIB1GfkrJn+9d67TJILBBYuMkkrJO68sEwVkwZM7iYxDEYRjGYxjEWAjLAReWAiMIgj8sBF5WEZYCMsBEWAjLBMFhTjgXcDU4mPMmCY8DRIoRRAxEDEYMR4MEAYkSERAMEQNeIBggGCYxBdiCouhiDFxiC64ERYgqF5C6EFOMQYn+HmDzfxi8QXF0LsQWC8gADIxBii7i78czktkuOYSnJccz////4gr//4guMVMGwALJF5lnP9BvppCIDGyCIHCQBTAAIAcOhiQFZiAIBkSUx6VdZvElhhWFZiyHJhwHBg4EJhUBxYAEwgDowgA8wOAEwPA4w4A4w7HMwAA4w5FUwbgHDC8DhNXyFkxLgVzAOAqMFQDgwnQVAPkBANdgGgQihBiEGDwiOAMAACgQuUDCARAhGuF1wTABdaJgGrAwkKmPwuchBc4ZUR4LkDBIYHAw1kDjBBvBgWN3jcErIQhSEkuM2S4EgYnQc0UuKRFzksLvFY8NXS6Bjl4oYVkVj+SvyVHOGYHMxlpL/kJ8fyF//j+Qv//OC6/////////lksS0WflrLBYkXFUWSw//PkRP8gLgMWAHfUgDqsBiwA7qkwWctDoA0zQG4P7BvvI2jZBECIYDgOAEw6CEwrEgxuG4zsH440YwzQPkxIEgDFmWkBwcGJIHlgAQaHYNA4wPAEwOA8CAeYdisYAAeBQhMQAcNGC9PqkNNpRyMDxJAwgGQgQHXAmgdmgAqJKMFY4sDzQgAIBAhCTFgCWASbBkQCbPqDYNbaPBjlQe5D5QS5bgOKTDBoa+bj++H/74qdwdB8HfJnekyDSET/Q1D7lyeh+Kz4atg2SAIjCxcVgVn+SvyVHOGaHMxl5L/kL8fyF//j+Qv//DyC6/////////lksS0WflrLBYkXJcslgs5aTEFNRTMuMTAwVVVVVVVVVVVVVVVVVVVVVVVVVVVVMSI/2yNkbKX7L8F9EAwMImmIGjRmjxGiRQieQDPLeoDQKU+ERMQMEYIgYCMDDECIDDGCMDBECLgwTAMEwESnAZFCKAZFSKgd3UjAZFCKBEigMIpBiYgxMfBgjBgjhERBERAaiEfGIFjgN0xBUQUF1GIIKhY4ILh5AiEADROAc1QDhEFkAWRB5P///GJGJjEEFhdgYKIEG6YWOjEGJF1///////w8oeb+ILRiC6iCmMUXYxBd+MX/xd///////4xO//PkZMAY3f0SAGrVSDEqyjAA9qcsMXEFgvHxdCQCpfldr/P5JZIhgyRAKowgFKwI/MCMCLzCYCYMrpkgxhhFTDJB7MAgC8wVALvMCICMsARmBGBEVgRlgCLzAiAiKwIysCIwIgIvMJgU8yBXsjFPCZMJgJjysJg0SM0SPytEVo//0AyAb/BhE6jxAMomolJWqP4/7JFTP61eSsi9sy7BG1Xa2RdjZGyNlbI2T2z4xOMQLxGJ/xzSUJaSg54auAxaE2hlSWJccwlBz8l/Jcc0lCXHNJaSmS/ktJf//////EFlTEFNRTMuMTAwVVVVVVVVVVVVVTlF/LTe1VqrVFTKmLA8rHmPHeV3yu+ZZlmZZyicosCf3lkWCz8yyLMwsHUsBZ5haFhYCzywL5i+L5i+L5i+Lxi8LxlmWRsD9549KJygWZlkWZWWRlkWfmL4vf/lYvlYv+BgvQLLTJslpDDEFy0xaZNgI8DPBnwZ/+DOA+8GeDO/wutwusGHBsGhhvDDhh//CPgf//8Lr+GGC6//8NWYrArOKuGrv/FZxVir//DDhh/////////x/5Cj/IXFyD+Lmi54uQP2i5ZCEJH4hCEFyj+QoMARLACCAb/XY2VAi2QsAXmAQBeVgElYERgx//PkZOwaLg0OAGuyTDmKyiAA9ukogRmBGDEVhMmKcEyabqbhldldGEwEwWAmSsJgrBPKwTysE4rBO/zCZCZKwmf/ywEyYTBXZinQ0mm6EwYTATJWEyWAmTIyM2JiLBEZERmRkZWxFZH5WEFZeVhBhASYQXlijLAR/qMqJqJIBlGEAqiXoB1GFE4WQAZAiDEweUPLhZB+IKjEF2ILC6EFRBWLsYsYgxf4eSFkQGnIAHIw8/CyHjFxiRdDFiCwxBdCCmLvxd8cwlCWJbjmkqSpKkoSsl5KkpkoSslCXh5P/h5VPKPMeOLA9qypFSlgcWBxunZYdmPyljJ/lgLLysi0yLZCzJLBeKwX/8w2QX/KwX/8rCy8wsgsvKwsywFmWBPzE+KbLBTZifCfmJ+J8VifeDFkEVlBizBiy+ERbCItBh1CItBgtgYPBwRBwGDweDAd8GA+DYMDDACBYAZkAYWCwXWDDBdcLrQwwYfC68GweF1wusF1giFvhhgbBwNg3/BgPA0gDwMdg7/8Lr4YYGweGGwuv4YcLrfC64Yb+GH8MN8Lr//8MMEQt//wuvBsH////hhuGG/xWYrGKuKyKvFYFYwiAA1b+GrxWBWIqjBKAwTYTY9Touf4hAAVMWlLTgQD//PkZP8dYgsKAGvVOjoTFhwA9ukkAwLQLPLAFphZBZeZKDlZWKoVgvlYbBgvgvmBYDoYOgOv+WALCsF4wXgXzBfBe8rBfKwXisT4sFNGU1WIVlNlZTRWJ+WBPywWG6FpW6FgtKy3ywWFZaYeHGHh/lgPLB2Z1eFYcVh38LrBhwwwYYMMDC0LrhdeDYPAy5cGwf+GHisirirFYFWKwBgQArMVkVYatirDVkLr+F14Ay0DYFoNgyDYO/+KoVQavFWKqKvxWcVn///4qxV+PxCEKPw/kIQshB+H4XKP4/C5ouWLl/IUXOP0fx+VTEFNRTMuMTAwVVVVVVVVVfKx3/5aVUpWADgZYHmOdmOHFi+V3ixegwWYGYB9wGlAWQGLMWXAw6gsBgLQYC0GAt4RFkERZgwWQRFmBiyFkBvvsaBpQSiBiyFkERZQNatA+iwIrYGtWBFYEVsIrYRW4MWAxb4Ng7DDg2DwusF1wusGHhhwBSwXWBsGg2D4XWC60LrhdbC63hdaAMuDDhdcMNwuv/hhgwwMLhdYMN/FYiriqiriqiqFZFWKvFY8VfxV8Vn/FVFUKv+KrxWMVYauFUKr4qhVRVfFVirxV////8VWKv+KxxVgITiySBCDnLcv1GPKwBLA//PkROYazekOAGrUSDa6/iwA76sAOlYAFYKGCoKFgFDEYdjEY2zQ3kTKgRjBUFTHcFDBQRjBQFCsRvLAKmCgKGCoKmCgKFYKmCgjlYKGIwjmAoDsYCoOxkbyXGLeG2YIwCpgjA7GCMAqBhUKgYUCoGFSMDAqDArAwqFQiFIMBGBgkEgZ7F0GAiSgrAZUc0c8lpLCcZKB5w8wRE4B0EDyf/E0iVxKhNRNBK//E1iVCVgYGAwDAOE1/4/8fx/H7FyD/IX/4uX+P34/SF4/+S8cySklCXHPJYcwlhzJLSU8lCVVAwXJslpPaqqZqpYAAwBBEtIWAWLTlgLDCwLSwFvmL5sHoGgG8SomX4WGFg6mFpfmB4dGBwdFgDzA4DiwB/lYvGL4veWBf/zE/E/MpqsUsCff/4MWfwMHg8DBwOCIOAweDgMHDsDSAO4asAwCAQ1YDADgOAIauDVkNWQuuBhYYAaYGINg4GwaF1gw8LrwwwXX4Ng0MMF14YYMMGH//hhgw4YcAYxQuv/w1eKxFXiqisfFXDV8VX+Gr8VcVj+KwKv4q8Vnis+Kr////////hh/FY4rP8VYrIq8VkVZlxeaoEGXhKAYsCCAUwgJ8wkuOiYjIiI2JjLEyVzJzMwYPKDy//PkZP8bVf0QAHfVgD+6UgQA3+qQmH0B8xi9ZfgY/aDyFYPKYPIDyeYF0AnmBdgXZgXQCcYF0BdlgBP//MEUBFPLAIoWA+kweUHkMvwgCjD6A+cweUHkLAPL4M8v8DEYiA1GIgiIwYIgiogNyqMDMYjBgiCIjAOEQMCEAwIgYQCIBwjgYRCIBxNANCIMCIREQMEQGIxGB+VRAwRAwRwMRCL4MBOEQQEQSBgkEAYJBMDBAIBgJgwE/AwSCQiCAYCAYCAMEAkIgkIggIi4GC8DBAIAwQLwMEAnAwSCYRBMGAj+BgkEAwE4RBH/8GAjwYCVA1ibPtUap61FO1P+BViwsdth2Wli2BizFkBsfsYBgtKgEQ6AYLAWAwFkIgtAwWB1gYLQWAwL2DAvBELwGF4LwMFkBiyFkBiz5UBmAMCDBZhEWQRFmDFgGsWhFYEVgMWBFYDFsIj4MHQYOAxzvhq4VjDVorENWRWA1cIBC5wFjACwcXMIBh+pCxc4uUXJisisYrEVgVcVYrEVn4rGKyGrxVAwAA0jFVFWKwKrj9x+H4fpCkLH4hCFFyR+kJyE/////xWBVeGrA1ZxWRWOKr+KwKx//////FXxWf//4rJgygHlgA7xCACqUrAAKwADARAA//PkZPAbngcSAGbUSDsKmhAA9u00ap5gdgHmAeB2Vg6mF8DoYFgFnmRaRaaO+z5XAaWBPv8wXgXysF4wXgXisF8sAvFYWRYCy8rCyMLILIrCzLBFvnIXaqVo7lgi3/K7M7Oy////Kyw3UsKywrLDLHQ790MtLf8DFpaQtIgV5aXy05aZNhNhNgsC5ixiZilIF+WmQL9AtAorF02f9NktIWkTZ8sC4GLf//TZTY//TZ9NhApAvwMXAUXTYLTemx/+mz8VQrIqhWcVYDgAhq8NX4q/FWKxisirDV3FZFVFXxWMVgVkVjFYFZxVQNFiLUxBTUUzLjEwMFVVVVUtKmx6p2r+VgFqNmAUAUWkAwFoEAWMDABYCgLlgBcCA/mAsD8YMiHppkf5gsTJaUrEsCAuVhgBQwLTgYLk2AKGPmGIYAYYTGUfjLMMDZqNjBZgTOVtDEsfzDEZTBcFjBYMU2DEoFiwGIEDEwXBYrBcDBaWlAwWFgF02AMFxaRNn/C68LrBdaF1wbB3hhgbBwRsAO8MOF1wusGH/EWiLiLRFQuGEViL//wbBwRsALYLr/+Kr/FV+Kr/////xVfFUKsVX4qhV/4rP///////hqz4rBWBP/vmztnD5JtqIBACgUALU4MA//PkZOUaJfMQAHuzWjfqyhwA9qcwUAUwRwRzAnAF/yxFuZ0JMBiRBBlYQfmAIAKYAgE5gTATlYE5gTgTFYUBggAgGCCCAYIIUJggBQGCACAYdwd5h3h3G50goWA7jDvDvMO8O//OBBODBOBB8sQP8xYsrZqcoqhQWZ9kEPkV0VFOFOEV1GkV0VkVkV0VVOPRXU4LB8IKoqqc/6nKjaKyK/s4fNnD5M498023yfBnTO2cvg+b5xF8RTEVC4QBXoMUFw4iv43I3fFBBwhQYoAbg3cUFjfG5/8UH/+N0b///C4ZTEFNRTMuMTAwVVVVVVVVVVVVVVVVVVVVKwO/0Cv8tOmyWnMLQtLAWmFoWGL4vFgXiwbJmw55i+qh6BJZ2OqpyWLxYNgrF8rHQwtC0rCwwtCwwsC3/LBZlgsiwWflYWZkokoGY8l4ZKIwJhZhZFYWZYCzgy9CN8GX+EVoMWBFZgfVaDFoMWww0MOF14Ng0MOF1gwwYYGwcGGDDBhgBsYXXC63C68GwaDYP/wMeP//hdcLrBdcMMF1uF14Ng3+KqKsBoCKsVjisxWIqv///ww3DD/DDfhdYMOF1gut/////////w1fDV4atFWKoNWcVQrAat4rAcBA1ZU0Hwc5LVfa//PkZOcbbf0KAHfUgDXqzigA9lssqHABKlMAABAsAdmB0AeWADzBeBfMNkF8xVFLTBLB/MDEBYwMQFzAWAwMDEDErAXQLAgCxacwLALDAsAt8wLQLSsCwrAt8w2A2TQ3DYMVUF4wXg2PMF4F4zjjP6K+jPOLBxYPKzys4tKWnQLQKLSAeEDXemy1dUipFTFYPqmaqIQGqep+D1rqecpyfg1yHI+D4NEZHSI0Mw6jqOkdB1EYjoIzGYZx1GeMwzDoDoDWABejODrGbGYdfxGhn+M//GcRkZuM8Zv/46//8Zx0BxGol7Z2zNlXYX5AAWWAgwkIKwk05PNPuzTk6EUjAZFbuAbpwxgYYxRAYowxAYYwxAYIgRhEEQGCMEQGCIEQRBEEQnhEJ8DCeE+EQnQMihFQPh5FAYRSESKwZiBiKBo0YMRhFEBokQGjRAYgSDBAGIEAYgSERAGJEhETh5A8oWQhZAHmCyIPMHkDyh5A82ERIGvEgwRhET8YgxBdCCouoxYXmMSLvEFxiRiC6GJF1F0F4ARFg2QILRdYgrEFxiiC8QWGJEFBBYGyhi8XfF1xi///8PJ+ILDFEFfxdDFCx0QX8XeLsXcQUxdYgrF14u+Lr/8XXxiC7F2LqLsYuLvx//PkZP8gtfUOAG7USDMyyhwA9pskBaMT8LxF2gWmymw1dqrVWqqnLACJaVNkDAxGBYBb5g6A6eWAszCyS9M+4YArCz8rCzMCwHQwLALPLAOpYAsKwXisF8rBeLAL5YBfLAbBYCyLAwJn3DAGMAMAVhZf5rVvla0r6msWla0rWFhYY92Vu/8rHmOdlgd5WOgWQLYFoCwBYgWALMC2BaAA4AB8C1AtgW/AtwTmKgqioK4qgnAJwK4qCpBOYrCvgnYrioKkAIQJwAEAE7FYVBVFWKkRgZhGR1xGxGI6eM46Dr8Zv/////4qCqpMQU0x44rH+IAKpFShyEQADQADdO/Mdk/ysLIwsxgCsLM3KyUTeVHOKw2DBeDYMNgF8rBe8rBe//LAWRYCy8wsgsiwMCZTRTRifFNHhFWOZTYn5WJ9/gd68B37wRvQjeBl/A1i3hFYDOgMW4MdhEdhEeER4RHcDHjgYOgY92ERwMHYMHwiPhEcER/Bg+ER+DB/8Lr4YYLrBdcGwfC64GWlgwsGHww3ww/hdcMMDC2F14Yb///hh/hhsLrcMP/+ESwMLBdf8MMF1/hdeF1////8LrBh/hdbFYis4qsVUVQrOGroGQAgNAhViq4rArAqxVCsmIIVi+zt//PkZPQdxgkGAGvUOjRKDhwAz7SQ8Xy8OADgwNd5YxLFpYsK7DBSAaMFMI0xphNTUIEHML8CwsAWeYFoOpYAt8rAtMC0CwwLQLTB0AtKwLDAsAsKwLDEHAtMQcQYxmhUDOaUJMeIHUwLAvzB0AtLAFh9VvmsWmtW+a1YVrPK05WF804Uwqc0+jywFMKFRURVRVRXCC3qcKNqNKc+1ZUogQmAANWVJ7V2qKlVP//7V2qNXasqUQgCsC1T2reqVqjVfVI1Zq3tVar6pFTe1f/ar/tVar75vj7OHxfL3x///3xqTEEsC//+qRU4cEHAlgQsClgUsdlfZY6MA8RIwDgDzE+WrMV8G4wbgHzALAKCBBDA7A6MDoA8sAHFYB/lYBxYAOMA8DowOgDjAOA7MA8JAwDhEjE/CRNNEsYwZQOjBkAOMGQA8sAdgY90Bjh4MHgY8eBjhwRHAweDB4GFCgYQIDAoGECgYQKBx04RCgwLww8Lrhhgw8LrwbBwNg6AKXC68MP/FXxVCrFZFZAaBw1eKyKz/irFZFWGrgHoIqxVCs/G/jcxujfG+N7G///FV//8VQqv/+GrQGgH//iqiq/////8b///FBjc8boGAtLS+m0ztnTVg4CJU/lgA4wDwOyw//PkZP0c3f8QAGfUSDhSLhgA92a8DqVgWFYFhhfAWGF8M0ZhFPB3wmpg2DRkYRhWRhg2DZYFIsA2Vg0YNg0YWBaWAsMLQsMLQsLBfGOgWmFjNmqE7GX07GgxfmqA6mFgWmX46FY6mFoW/5WFhYCzywFhgeB5WB5WBxYA8sAcZIB2WAPKwOKwPLAFIqIrqcqcKNeo0VgWioo0pz5gUBQQHqKinCK6KinCKiKqjajURYRYLh4iuIvEWEWEUEW+KqGrw1fAeADgEBoArAq8VfigMb0UGNwOEN74343xuDcG7G9VTPKwBP9qjVVSqkaoYAgApgCgCGAKAIYAgExgTgTmAIDSYAgIxh3iZGCOxeYmQExgTATGCOCOYEwAhgTgCmAIBOYE4ExgjACmAKAJ5gTATmCMAKYI4E5g0gCGBMBMYIwIxiIj+mwYUGYmQAhgTB3mAKDQYVoI5XH80wUwqc4wUsRjCBTThPLAQrCeWAphIxhAv+mz6bJaRAtNhNj//2rqkMARauqb2rtV9U6p2rNWhHCMEbgG8EQEb8VBXisKkVBUACAAEAAIYJ0Kv/8E7FQE48Vv///+Kgq//+CdCuKorCr//FcVxV/////8I///FRNktN6pVT/6Y7lBwERgAgIG//PkZP4dqfEOAHtNljb69hwA9lssAAAgVgWlYFpWBaYWYWRYCyM+8+4wvwvjEHAtMCwC0wLQLSsC0rAsMC0CwsAWlgHQsAvFYL5YBf8sAvGC+C+VhZFYWRn3rGGY+FkVhZFgLLztsO2wsWFizyuz/Ai4GvLTlpgKsV4IFFpvau1X2rtWaq1VU/+1YQAeIYA6EOBDg2rtUauqVqrVVSCtisCcCuK4JziqCcRWFQV+KorCpgEwJ2KwrxX+M0dYzRniMiMjMM8dMRuOnjMOozCMjp8ZozDr/jp/46DNjMM//jqqTEFNRapApNlNlqjVVSpiJjKfEAADVQ4EAsAWFYFpgWg6GFkFmWAszGAcrNYwLMrCy/zAOAOKwOzAOA7MDsA4rAOMF8F8sAvlYL5YBe8wXw2CsLMsDAmfesYYWYWRYCy/ztsLFhXaV2ldvldvlhcDXFp0Ci0wFXLToFeqZUyp2qqn9U7VfVI1ZUqphACYAKpmqNXVK1ZqzVfVLBzkQY5fwe5CDa0fg73Lg+DIN9yoy+kGQZQOUosXqGkFSvp77f78voMw6cdBnHQRkRkRodRnHUZh1jpHUZ8Zxmjp4jQz46jNGfhoB1hoGYZ8Z//+OozjpjNHXHTEZEYGb46DPx1G//PkZPogpdEMAHstmjACchwA1aiQeM3x1jp4z/g7wJmVp/98mcvkzhnCbRhAphQhpwhXBK4BwIAREEERBgZInQgboDhBEQUGCCBgGgYFMDA2BqBgaA3gwQWERBAwQQMJGBxAiCBxBJEDBBgYgxBAwQYGbNAdI2BmjUDNmgibAzZuBhQoRTAwKBhAkDjpwYmAwoUIhRFMRcLhhFuFwwXDCLBcPASKiLxFxFxFuN8UAN8bnFBDdG6N4UDG5+KAFARvDfjeG+N/G+N7i6JUc0lpK+OaOYShKyWJYlpKkv//8UDVKwD///VKtZTynYhABMBACEwEALjAsAtMC0HUwLAdSwC8WAXzO8DYNSwF8rBfLAL5YBfKw6MOwPMOgPMDgPLAHFYWFYWmFg6lgLTC0LDCwLTNkXjNhVTsSxitVSs2PLAvlYWmFoWlYWlgLSwFvlYWFYWJslpUCjBcFk2S0/oF+qZqipWqKn9qqpWqtXar7VWrNXao1dq6p2re1b1SBq+KuKyKsVkVkNWCq8VYqxVCsYqxVRWRWAMghDVorAauFVxVx+IWQshB+D9iFE1j9IWP3ITIX///BsHhdf//8f+QkfshfIUSoMVkJIWP//4//5CePw/Q/QP0ITx+8hfj98hY//PkZP8f/gMMAHu0bDfafhQA9qUo/lYDf/6jfuQg2mKWADywAeVgdFgC0sAWGBYBYYFgFhg6hfGV8jsaqRX5g6A6GIMDqVgWFgCwwdALTAsAtMC0C0wLALCsC0rAsLAFpgWAWlgCwwdQLPML8L81CTCTItK+MQYQcwdALTAsAsNYtK1prFprFn+VrCwtLSIFIFpslpDlFvLSlpk2E2EC02UCvQKTZTY9AuFwwRsLhAFU4ioi8LhRFOFwmIoIoIp/G8N8bg3BuDejfDKAVYUGKAjcFAigBuxvCgBuxvCgYoAbg3ON4bnFAigBvcb3G5jc+N7G9jeV4gtiC4xQICoRRAaJFA58/5hMBMGKdOoYTAV5YAnMFQD0wEQaiwBEWAIysCIrAjLAEfmBGBEYEYERgRgRGBEDEWAIiwEwYp5ApinoTmEyKcYpwTH+EUQGjRYMRwYjgYgQERIMEgwQDBIMXgwRxBcXYxBdwseGKIKxdDFF0DdEYouxii7GJi7jE8PLDyh5Qsj/jF/g3QjFF3xi8lyWksSmOeFsBV5LY5pKksOdkvkuSo5pKZKktktjmSWyVjmEv5KZLeShKyUkt8lSU8c2OYOcJvHNL08dOZ3Onj2enC/nD/Oz5clwf4tv//ql//PkZOoc1dEOAFPUSDPymhgA9mzE9qpgAAAFYACBZaQtKYFoFpYAtLAFvmBYM0YFkPBWV+WAdCwBaYFgFhX2fR5WeV9mecWLTssK7TttOy0sWAYdAWgYdSDgeLmogZUA6gZBg6gYvwWhEFgMBbBgLQYCzgwFgXWAEAvwBhLhhgbB0VQqxWQ1dFZFZ8MMGHBsGgwCwYf/FzD+LlFyj+LlFykILmj+P8fxcwuYfh/IQfh+H4XIP4/ghAyIAkJISQg/RcpCC5JCeQpCcXL4/ZCC5h/H/x+IQf/8fuLkIQfiEgwFikxBTUWqqi0qbP+1VqvoFgYLSwC6BRaUwsC0wtC0sBaWCyLDAH98oHFo6lgLCsdTCwLTA4DzA4DywB5geHRgeBxYA8wOA8sAeYHAeYHDKYHjJ5i8qpqoqhi8L5i8LxYF/yweZ559nn0d5nnFg8rOQKQKLSFpDWXA8CBXoFCqK4rgnYqgBCBOYJxFSCcAnYqAnAJwK4r4qABAFYVQTiKoqwTkVQTqKoq/xX4rioAAJFUVsVIrRnx0EYjrGcdOOnGbxnxGcRr4zDPiNjPHQZx1B2x0Gb8dP46R18Z/8Zx1HX4z///x1jqM/HURkNZgHAHf///gYC4tKVgHmB2AcYB4//PkZPgcndEMAHctkjerbggA9ltIMnlgF8w2AXjCzCzMYAYA2XQszjJJQKxgTCzCy8wXwX/LALxgvAv/5YCz8sBZlgLIwsxgCwJ8YnwnxqqwGFgT4rE+8rE+LAbH/5WC95WC/5ndlfXmceWDz6OLB5WcVnps+gV6BRaQtKgWgX/+WnQLA1v//psFp0CkC0CwLYFiBaAA5AtgWALWBb8VBUBOhXBOhViqATxVBOQTkVRVFfFQVsVBWiuKorfFeCcAnArxU/xWxXFcVRXitFXxWFeKv4rf//FYE74rf8Vf/8VK//8sAEeVgEFgC4wCACPMCIGP/KwTisLssDyFY8pjyT3nZHKaishuVynZBEZjEZWYitRmohGWBEVpjywmCwmTTCZNMJgseQ/J5D8vkK/IWPL/muycVrorJxk8nFZOKycWCf4RRQNEjBiMI4wNEj4MXhESERMIiIREeBiBAGJEQOouBgjBggGCQYICIkGCYecLIAsih5A8gWRgwiHlhZAHn4eaHm4WRw8gWRAaciFkIeQPOFkHDz/Dy4WRAZAiHnhZCFkIeYLIIeTw84ecPP+Hkh5A8geX+HmDzBZH/Dy////w8wWRBZGMUYkYsYogr4xBdC6/i6/+FjwgqDBP/7VV//PkZP8fddMAAHuUWDgTFhAA9lswTNXVIYAAABgAgAhwECpzAAAQ8sAvmC+C95YE/MpqscxzxzjDYBeKwXysF4wLQLDAsAsLAFhgWgWlYFnlgF8wXgXv8wXgXysLIwswszGAjJM+4YHysLLzstO23yuwrs8sWeBry0haYtOVrJsFpy05acOCasqZUwhBDgVTNW9qvtXVOIQGr+qRqn+1RUyp2ruW5LlwYp5y3Kg1yYP/4NcqDHK4rxWFWKgqABH4r/iv4rxX+K//HSMw6iNY6cZ4ziMCNDOIwOozxmHUdBnjP/xGcdRmHWM468dRn4zjMM3/9RJRNRNRMwEQJ0AxgEgElYBBYAvLARJYA3MDcIgxMQNzDdDcNMZB45+tE2DDYrDYyJDcxiCMxjCMsBGWAjLARFZEFYbmG4bGG4bGRAbGbpElg3DTANjtDeDYWfys3TDY3SsNjKMIzGIoisIzCMIysIjCIIywERYCIwUBQrBXysFDBQFDEcFCsFSwCpYBQIgBgPCIcGABgYMCBhAB8CDA4MADAwiHhEOEQQiEDAEGA/iaYlXhigInE1DFeJr8XQgoLsQWGLEFRiRBUQVGILoXQu+LrF3F0LrF3xi4xcYkXX4xMXf///8QWEFhBaMS//PkZO4exc8EAHuzWjeDDhQAz6iQJX////+JqJV//7VmqqkDglTqkNAEQoGecZ3ZnnmC+C+WA2DFUQ3M7wF4wXgXysF4sAvmAeAeVgHlYB5gHgHFgA4rBeLAL5WC95hsAvlgF4wXhVTBeDZMF5xoxzhVTDZBeKwXiwC8DFgRWAxZBizgY8dgwcERwRHAweER0NXANAhWRVisCqFZDV3FWGrA1YGrQYBFYxWBVirFZDV4mgucXILlFzi5SEDFA/kKQpCkLH4hR+IQhZCRK4uQSvi5CFIQhOP0f+P4/D9yFH/ITj8PxCD9/H/yEITkLH8fo/yEyF4/4////j8qTEFNRTMuMTAwqqqqqqqqqqqqqqr4rIrAatCIAQiBYGwcAMC4GAsCILQMOodAYF4GBeA0lw2OSxeKzZLAv+Y6hYVhb/lgLCwFhWFvmFgWGFgWGFoWmX46GFqDnqKDmqKDGFo6mOoW+VhYWAtLAWFgLSsLPLAW+mwgWWlTYLSAQFi0yBRacVhXFcVRXFYVATsV4riqKoJwK0V4qiuKwriqKozxGxGhGBmGcZw1DpHSOsZhnGbHUdRnEYGYRoRqIwMwzCNcsHuVx6lWWlRWVysqKo9+Wlo9iwrlZYWcq49yrLP46//4//PkZNAaeYcMAFutWjHjDhwAthsk6//x0/h+o/hq0NWAOBCAKBYAQC4NgyBgsDqBh0PGBp2IMBh1BYEQWgwFoRAcBg7AcDAHhEB4GA4BwRBaEQWAwFgGC0FoRBYDA6wiYEDSjY0Iiy/lbje43OLDze//LTIFoFIFoFFpy0ybKbACCCcQTrFQVhXwToAIAJ2CdCrFfFSKpYVlZYPUegnw9h7lg95VHqWlg9Sse5UVFY9x6joJ8W8e0sLMsyorKy2WlUsyss5bLZXy2WFZaV8sKyzKist+VFZZ8syyWSr/lcsq/GKDZYRAwEQTBZEBgQBNAwRBjAwxgjCImAMpwmAMp6BQObpT4GU4TMDCeE4DCeE8DCeE8IhP4MEyBiZEyBiZEwERMhEigGRVIwHdxI4GRRI4RIphEJ4RCd4MCfLCI0aLywjK0ZYx/5WiBhBRJRlRPywRQDqMKMoBEA6jCiQNTKJ+gGQCqJKMoBlElE1E1GUAqjP+gHByFAOon//6AZRL13rsbK2T2zNnXe2cRmC/f+2ZsvtkXa2YAGCIEoJgmCUEwTgAQAPAA4IgAEEeCMEwSABYI4APwRAAQRgAwRgigl8E4J/gl/gn/wR//+WABrzABAAQwAQAEMAmABDAUwFI//PkZP8dLYkAAFtKqEXTnfAA/ukksADRYAjCsBB8rAoP8wto66MlUFIzA7wO8wfwH9KwO8wIICDMEiAgisCDKwIMsAQRYA7vKwO8sAd5gdwHeYHcD+eWAtsy8JGwMLbC2zC2gtsrC2vPu7z7+4+/uK+/yvu/zQUEroDQUE0FBNBQTQEDywg+ETQRNgZs1AzRrCJvBhqETQGaNAdM3/BhoImgMIFgwKDAgMCAwLCIUDChIMCAwKEQkIhAMIEBgT+EQgGnCgYULgwJwYEhELwiEwMIFBgT/BgXBgXC4QLhAuHC4QRYRXEViLBcIIvEX8RULhsRYRSIqIr4ikRYRQRX/8RQRb4i8RaIsIvEW/+ItwuG4in4igit//9Tr2dOi+AyBiqgyAZgADpYBwwBAAxuG8sH0VjcaYm4YbBsVhuVhuYdgAYAA4YOA6WAcLAOFgASsATBwADAEOjAAHDDoHTG4bzPthjYeFDG8biwN3lgbiwCbrpYBKwCsEsAmAAVuJjJiKeU78LUKdKdpjOQ5EGIqqqQdBzkfBynLlOUqsis5TlQf8GfBkG/ADBTgpBoMg0GA0FcGApBkFAVBgKABQAgV+MRiJuD0T+MA9iYZE3xNjInB+MYwJ+MDH+Jv4mxgZE4//PkZMkZcXUKAHcnljVTEhQAt2Kwn+Jv4cMG4BFwEALEUCIBQMAgBQMAoBQYIMDEGIIDEHYA2nBsrI0wbI0wbBswmEYwEAUwEAUwEAUwEATzEEQPMQRAKxBMQRBLBBGQZBHMKRHgJBFZBeVkGYpA0WBT8sA0YNg0YNg3/gdoHLA5IMsGSGCxQQcIbw3xQON3G5G4GBhQQ343BQQ3BQUb43BvYoIbsbg34oKN/jnjmkqS45o58lhzAuiKUHPkoSpLZK5KEoS0lyWJYliWJT453/JQliW/kuShLkr5Kkp5KY5xKyXkt+SvyV8liUJVTEFNRTMuMTAwVf//9TuDXJRWKwAKwBLAOlYKGCgjmI4jlgziwZxt23Z1AfZYG8rG4rG8x2EcrBQwVBQxGBQwUBUrDfzDYNywGxhsRBWbvm3TdlfJmZxnmZxnlZnGZxnmUKGUK+ZQqVxysp5WUMCAKwJWAMABMAAN0d/zAgSsP6nwwemKmOp2p0p2p38HqrwYqr7lwequ5EGKxuXBrlwb7ke5TluSirB0HfB8HwY5MGuT8H/B/uWpwMgoM+D3Ig+DPg+DgUwU4Kgzg2DQVwAvGOMxkTjImE4zGRNE0ZxmD2Mf/8Ff4N4K////wep0tJqqpTAE//PkZOkbpYMEAHdHmjXLEhAA7lssEPLAWlgLfKyyOUZQOx1UMXhf8sC8YHB0VgeYHh0VgcYHgeYWBaWAtMLQs8x0CwrC0rLIrLMyy+8yzLIyzLMrLLzOOLHRnnGccVnmcf/gawtImwmx5aRAtAv2qqkVMqRUrVmrKkaq1Vq7kOWp05DluXBrluXBsGQZBozRmGeM0RkNA6DrHWM0RvjOMwjURodRGR1HUZhGxGY6S0rLR6R75UWFg9CwepUPUrKh6FRbKh6FuVFhbLSoepaWcrLB7y2Wlce4jH/xnx0//xmVTP//8sACGBaYDAOYLAuVgCWABMAQ78sBuYbkSYbBuZEOsaYT8ZuBuZEESWA3KyJMFAVMFAVMRgUMFBHLAKGG4bmG4beYbkSZEhuZEBuZuEQWCJPeN4N1jcNhQ3MNyJMiQ2899vPbYr3PbcsbeboBgd+YIBggGCCWAPKwExExVOlOgsOmKmMp8xh1PqdpjKdqdBhqnkxvU7U+mKmL6YwmolUTQTXErErE1DFXEriaiaCVCVCV8MUgL8MUiVCV4lQmuQg/D8QuQkOmH8XNFzi5R+4/EILnH8hCFx/H4fv4/j/kJH////9UpYAErAACAt5guJflpwKCxYLIsFmbAY8f//PkZP4cQT8AAHcxljnTAgAA7aUE3lmbAFkVlmVlmY6BaVhaVhaY6hYVhYVi+Vi8Vi8Vi8Zsi+Yvi+BiyFmETAgbHuVAYshZAYshZYMF9AwWAsAwWgswYC0IgtA/8D74H3QP+BnwZwNgwMNBsGhhwuuGGhdaGGDVkVgDARVisBq8IhiqDV4rIasFUGrQ1ZFUKuGroasFZisRVCsiqFUKoVkVnFYAxCKuKwKwKwKxkIP4/i5YuQfxcouUXJkIQpCkIPxCR+H4XKPwrPFV/iq//yEH7x/IUfh/8hOP8hIuaLnV/EUCIBBFQCgAgkAoLWQiAgDBEEUDDEAkIhPCJMgMXdID1sIywEZhEOpWERggEQkJhYBcwXAgwnAnzCII/LA6FYRmEQRGO4nmO58mp0XmTI7mJwnFYnFgTwQERgQBBgSFxWBJgSBHlgCPUZKwyQC+YEgEDgFKwJUTUSDANk6gD/oWpkr1f5pnqeQ4sTSbPOhDeSQn3JK0j0ry+vk8J4WEsYtfXu0NC+vtC/15f/X0MFIQ/tPQz9DvKfPl80r5FqhUPpn75efd/5Gl4q+97T/2lpmlk/8n/n87zzf+bvVU8kmaO+lkfSSP5ZJvJ55p/J5P///2XoHQaqwLAouiWASW//PkRP8d8YsEAFuvXDdDFggA5p8kAsWAQWCcWBKfRdZik6lgRGIxGViMwiOjE4pUTBoIMTAnzEQj8sCMrEZnURG+Seb5OxY5xYfJk4nFZOLBPOwjMQ7MSuKxJiRHlgR6jJWbQD+WDgOdeomokSUjyTIYAwqpDENLD2hDV4nhtj0i0oabJJ2gekkrR15eXmhoXl86uv9paV5eaF/lhX/19DxP0OHqaDZQw2uh/VKi6r88j1ESyPZ3zxofNL7yd7J36////NLJ/5f/N53vm6Gefv5Xkk/76WV7LK+kkm8vnnn8nkVMQU1F/EVEVC4cIgEAwCgmAwChHCIQQYEAIighEQQGmF0IGh0UAMCCBhBFABhACABhoBOBgEAIBgnBMDAT/BgQAMIIQQiO8DHef0DgrSMDP4O6DB3TNUiwaM2aM2aLBosGis2Zo2YQIYQL5WELAQ0ycrC/5WKU4UbRUU5RU9ThTj/UQZ36iCSaiDOmdpHM7fL3zgzAKwCgBYArwC4cgzDkAsHYdDoBcGAC4cBkAqDGAWwbBHEaKYpBtBuBuBtFANgpBsFOKBGFIoxHFIjigG6KfBsEcUCNiNigURGgx/+HMGP/gz///+zpJMFDjJBjDkkjTpGzN0zpmywCAWA///PkZPkb7YUAAFtKljkykgAA17KQzFfG8NLUEAwQQQSwCCWAQDAnAFMEYAUrAEMCYAQwBQBfLAIBggggGCCCAYIIUJYCgKwQCsKEzOA/ysKEwQRX/MKEEExBDEnMUQrFMQUrn/zEnMQXzFFMQQsCFYpYE8rLU4UbRXUaRWRU9FT/9JB8XwTafJnT5M4fH3yfBnL5/6iD5Ph74e+D4e+T4vg+f+zpnfvi+TOmdM6////Zz7+SWSSZ/JI/sl/38k3ySTSX5NJf9/38+Syf5PJZLJ/+TyX38f9/v/5PJ5K/nv6q//9RL0rC1SlbxgIAdAkYBgAhWASYAoF5gEAXmBeBiYii1pmHhhFYFhgEgXGD2AQhKMA8DkGgcmCYByYBwExgVAgmCuCsYBABBYBBKwCSwAKYBAKZgPDDmfi6AYDguJh3BklgAkwCACTELzEHzEwDehDCJzCBCsIWAiKxAHQfMOGUWK59HRqcCQdNBV673wZ1SLteVnbOVoNeLAEWAqdM7hfsfehyPfBpr8NMg5YN/X/krs/JPd3/k1Gzl6sp+TODJpOPCY1yG5LD0lfKg9//knx2TSeTvi+ckkkk+TyX5I/kmknyf/98n+kU78N0MmoJN9FQRr418n+TUdC4RYE///PkRP8hNYUAAHtHnj6LCgQA93KsiAQf8QiDEEPh0Qf/+o170J/DAAK1kjSsAkwMQASsAUwAAJzAEAnMCcVIw7kADWxRysrzAUJjGkBAgXAwPEikiDB8CTEkNjCIIzAQBCwGxWApYGAwEQMzmVU/8uo3MJUzOK0sAKYCAKYk48SYqhjAmC4YIJWCWAUxSYVIqmCBmrlYZWGp9nMKY2zuDXJTEZytNyHKhLkNUYNtLVgXtEYO5HwbKGsOvB0YjUYoH3+h9+P+jo8oS5L7Uc9R0Td41B8aoIxQdoPjHyT3Fo6KT77QySSfRUH0MNUdD8n//7GX1fT41Q0dBR/RUEa+NfRfR0dDOlgX///+hof/////6Ch/6H6L6P6GLBH/+on/lYQYSEGXlxlwQb0qmnpx3SeafdeWAmDQnNxNCcgQrFPLATBWEwYcoEZhRgxGBEBEVgxFgCP/LATBYCZMJkJgsBMFYTJinFdHOoKebJJApWEyYp4TBhMhMGF0Cd5hdAnlYJxYBPKwTywCd4MRgaJEBokYRRBFEBokYMRYByMPMAaRhZCAanDyhEiDCEPMHlCyILIQ8oeaFkYByEPOHkDz4WRB5A8weYPKFkIeQPMDCMPLh54eeILjFF2IKCCoxYgo//PkZMYhaY70AG/USjbqmewAtq00BmRQN0hdBeQxYgvjFGKMUYsYnF2ILi7xBUXQuxijFF3xBUQXF3F3GILoYogqLoYkYsXYxIgrEFsYmLsPJ8PP//w8//h5Pww4XWAFAvgYOgHgYOwdAYZQHgYZQdwiT8DJ9VUDM0ZoDKgC0DF8L8DF+CwDDqCwDDqCwIi+gYLAWBELwRGzAwvBfhEL4RRYEUWgewNggaLUWBFFuWFhWsLCw1i0sLTWLDWrPK3RYHGOHm6HGOHnkHeVjzHjy0ibPpspsFZctMWnQL9NhNlApNgDLS03+WlTYTZQK9NgtN6BfoFf/+mx6bH+mx7VmrtVasqdUypWqFgAIQLVPasqdU/qk9qwrMViGrRViq4qhWfxWYq/4rIrEVUVQqvxWPiqgaLEWRwAWALP//QL/ysC0wFgFzAXAWMBcEowdQLTAtAsLAFvmRaRYaOzYJmPDAGMCMAVjAlYWZYAOMGUJArCRMDoA4wDgDvKws/8sBZFgYD/P+lsErR3KyLP/zDYDZ/ysF/ywC95nHGeeVn+Zxx9nFZ3meceK3ga5NhNlAv0Cy0vlpRAA1dqypywCHAGCB6p2rtVao1crAaqqVqyp2rNVauIQGrNV9U/tU//QL9A//PkZKodsgjyA3stqTK6jfQA1Z6Yr/TYQKLSga1NlNn/9Av/FSCdYq+CdCuCcxWgnIrCqKgrCsCddQ8RlKf/+iMEHDv8Cz////////As/+Kv/FSK0VP/xV////TYLSlpyw78xzox48x+QsDgiLIDFmYEDSjysGS8CIssGwYDAYgCgWAwLgXAGBZwiLIGCzCIswMWQsgiYADsZysDMALMGCzBgsoRC+DAv8Ihe4MAtC64NgwDAuDEGwYGGBsGj3TYxRgD2DVDFNI0uMYsy1AAQCqWZZFqWRZcsuWZojDTSb6Z6Z6ZNNMJk0U2mjRTCaGImDTTZoGmGqTYx02mk1+mExzR6a5p9NptM/ptNpk0OmOmP/+mumDQ6a/TP//TX6aNLmmaSv///0C/LThAC6jZWA+WADzA6BlMA8DswDwZTC9CQLB0BifiJmJ+AeYHQBxYAPMCYCcwBAJisAQwBAJiwAKVgHFYB5YAPKwOiwB0YHQMpgdAdGB2PabQiERYE+MA4LwwkQDvK+zPOK+iweVn+WDjPOLC5rLga5NktIBrU2ECi04cC1RqjV/EICplTqkaqIQYqABAFcE5FYVAToVxUFUVhWF4XBcFwLTwHcFpF8LSL+FoiMjOM8dR1DWIwM8N//PkZLwbgUT6AHstlDBiigAA7h8kAzjqOnHUZ8ZhmjOM2MwjGMwzjPHQZoz4vC5F74vf/F+DP/4P//TFLABKwGAYLJiqeDAOKw2MiExOKnXPyiJMNw2LAblYbmFADJjqeDAtDCa/ysNvLBEGGxEmRJEGG6YHaNoGmBEGG4bFgNysNyupYodKldPLFSuvpiBc6YinlOlPpiqdqeE8G0J4aKYFJE8TBppjq4nB89WK1XKxrViuPnu+rlcrms3GtXtbU1dq/dq9Wtf7W1NZuK3uv3Tp06VrtrdNTX1c19r7tq7W7d/umvq5W9067v913fdNbtXdr7X2ugZr4R0EdgetAetge9zBSCMLADZgNB+mH6CmYYpRBh+B+GuuZkYRg0xh+gNGLeA0YKQRhgpANmCkCkYDQRpgNgNmCmA2WAjCsBowjQxTBTDFMI0d4xNQUjBSIWOAzAUz2yUTAaDFMMQMQrAbPFGzGow1JTNSGzUho1LFLCn5jY35jQ0Y0NlamampFY0VjRYUzJyYsAhWTFgELAIZOCeWCcrBDBATywClYIVghYBTBAQsApYBSwC+VgpgpMYKCFgEAouBi5NlNhAtApNhApNj/8tMmymx5aVNj0CkC0Ck2UC/LAugUmwWk9Nn//PkZOkghRTqAE/bOjcqJegA92jI/TZ9AtNlNn0Cv9NlApNktMWmQK/y0/+mx/ps////lgAgsAEeDQEDARAQUTMAkC4wCQLjCiBVKwLzCjDIMiCso0hBOzB7B6MAgC4wewCCsVCsLzC4CDC8VCwFxgQBBgSBBgQBBj0d5j0ipheKhmQUZgTI55ZqZsOKplEPRneBBj2F4H7qgYmqBiRAGvXQNcJgYkQHnCyAA5AESAMIB5gYRh5A8wWQBEiHlDzhZEFkUPMHlDziVhikTQSoTQMUCawYGDFYmgmsTWJpDFImmJoJVhikTQTQSsYgu4uhBeMXBuiILRixdiCsQX4xBiRixii7F2MXi6GL4xJNj///KwGysBssANFgBssANFgBsrBTMFMBssANmEaCmYmhRBkLNcmkKLcYRgtxgphGGEYCmYDYDRgpgNGGKA2YKYKRWA2YDQRnmA2CmYDYKZhGApGA2GKYRgtxlgh+n425WYfpfBh+jvGA0EYYKYfhxo2Y3GGNDZqUYY2NFY0akNmNDRjQ2WBsrG/NTGysb8rGiwNFZ0VhxhwcWA8sB5WHlgOMODywH+WA8GLBiQNEBigxANVA1QDRYRSAqwXDQFWC4cRcRQBFxFIXCiLiKhcMFw0R//PkZNMemdrqAHtylCw6KfQA9w64XEVEXEWEWgyxFQuE4isRX/gxf/////////////////+EUbL/+2VsrZF2IEi+xZFsgBAKAIBYkAsIwGTBOAnMSdYgSCpkAamCgwVgowUGACJjBQLMgBkvoYZBQjE5hgFGGQyYZDBWCjO5PM7Mk9ByToS7NCEAAjUzUChGNDBQLMFCYsmAAyuxs7ZQAGCyJZJsnmGQUgTXcX5g/1GnIVh+D4MVXcqDYKQAAUgBgBgBgqCsGAAgwFMGwUBQFIN4KRCH+A8B4DhCHBweIIDPwaCnwZ/wbSsDu//8sAd/lYHeVgdxgd4HcYHcD+mD+iChg/oYSYxcGEmMXCkZgdxKqYgopvGlj+YJwNQgoZbYMXmKRg/hkJAYQYYQGElgMJMUjB/DDCQf0sAd5g/gP6YP6D+lYHeYgqIKmB3jFxjF5sMYYQGEGMXRQZy1Uk4aNw2VmSqEqhmPYpEYYSGEmB3gd5YEFTB/Af0wwgDuKwwkwO4H8MH8A7jA7wf0/7/Dd7uK3eWP4bv/p/z+FbvN3u43e7itZGs1l5YwBWsjWazNZLP/K1kazWRoJBlaCLEiLCCNBIIrQZoJB+VoIsIIsIM0Eg/8GIIIoMIoIGIOEUEE//PkZPgsJezMAH+VpC9CaegAn2h0UGEUHCKCA0EggigwigwNBIPA0EgwNBoKBoJBhFBgciQQGg0EEUFCKCCKCgaCQQMQfwYgoMQcDQSCCKDgxBgaDQWBoJBwig8IoP+3//qS//8Kd3//61f/+EUH/4MQf4RQYMQUIoP+DEF1C6i78GBhEIHwIGEHmBIqmBAEGCgKGIw7m0MbmD5YmLAdmAAdGDgAmBIEmFwEFYXlYEGBIEf5gqI5juI5goChiMChjsIxoaO5iPyBiMI5juI5YBQx2HcGRgOPGAypQGFQMoVBhUDKlAiVgYgRCIgDECAivCIjDFeJVErErDFQmsSoXYgsMUQWGJGIMUQXGILsYsYoxBii6GILsYuILYgtiCsYguuILCCogqMQXUXUYvGJ/GJjFF14xP/+MXxiqv/2y+2RspfgSBQwHAcweAowNAEBDYYagAYPj4Yik8Y8hobyeebvHMYFC6YuC6AgUEAWmEgEEIkGCQFEQsgAJl2mGoFGNghmJgOkBC+YeAUJpsGHGIyBMYBQDICCcEYGhgXgXmBqB2AQGhgCQrAbXaAAAkCDZF2IEkrQCAMWAFjAIABUZbOX1bmXsLABCm7ZGyJiF7lO1NXKU6g4vgp/0aXIWimN//PkRKQeKeDwAHfNij2TwdgA91rc7le5a1VquTB/uX7luW5ZfCD4OctToZxmB2jMM46RGw0A6w1R0GcZo6DMM///iP/xI4j/Ed4LR////Ed////xI4afEf/xI/GYZuI4dR0//U79TpTsMAMDALCyAIAWMEIAgwCQBjBNBRBIKBhlApmMWCYZPhKJq9B6mAsECYQAQJgLADGVQNAITzCAQx0FjBsIDIcOkxTE0Fh0PDC8bDJctfNQDZMv/+Ndw6MFgpMbgGC4mIBBImzAcGzFQDSsGkxTAYNywAynSYhWAwYE4CA8sBuAQIKwSU+FgHDAJT7U6RRU6U6QDp8qJIprv9synlE/U1Xe2b1El3+u9sjZF3tm9d3rsXcu5TzZmzrt8RwjxHASIjwIPBaokcSIjxHRIiPEf//4af8NeGnw0eBMP///4aP///+GvBo8NP/DX8R4j+GgSAkaUY//LAAP+VgReWAIzAjAiLAMZgngnFgE8wTgujC6BOME4Lsxki2DGSEnME8LowugTywF0VgnlgE8wTwT/MLsE4wTgTisE4sAnlYJ3mCeF0YJ4JxjJhdGg6xyZNYXZjJhdFgLowTwTys3M3NixEFZuVmxWblg38rCTCS4rCSwElYSVl5l4SVh//PkRIcbEejoAHtwljMb1dgA91q4BYCfQCg0RQClgRUZUYUTQCqMoBvQCKMIBUAqARRJRhRNAKokgGUTDywsiw88PMHkDyw8v4MYMfCIEWDD////gw/wi//Bh/////////////+DD3yfH2q+WmQLTZAgCxaQwFwFgKCUBQSwKCUBAFjEyEyMGSGIxkP8CiUBiWLAlgUFwKCwGJYwwBcwwEswWBdNgxKBcrBcCDKBj8Mfh+MsxkOHO7O0HpMzBLAwWmGILAYYQMMIGGNArzBcFgMMRaYtOWnKwWAgLgYLEC02ECvRW9ThRpFVFVRr0VFG0VwDe+EQEaESEQEQEcE5BOoqiqCdisCcCsKwJ0K4ritwjf4BuQiQjf/hEfhH/4AHf////////////gWv8Cz/CI/wjUC0C/9ApAstOgV4FAXMDABcCALGAsDIWAMDAXAXMM0EowSwZDULMfON0yMSizAwxmGIymJYLAYLTBYFy05YBcxlBYDBeWnQLMFxlMmSYAolGph/nQv3GzbaGTIlGWQLmCwlGC4YFgFgIJRhiGBhgGJiUGJWGIGGMtKWmQKQLLALoFIF+gUgWWl8tJ5YBb0C0C02C0oFoC0Ba8C0BagWALAFqBaAt8C0BbwLHwLA//PkZK0dtgTmAHutXCZzkeQAbxp4AHeBY/gWvAsfAswLHgWPgWwLGBa/8C3AtwLP4FvgWvgWf//AsfwLEC1//wLECzgWeBb/gWcADuAB//gW4Fj/AtfwLUCzAtpsFYXAwtLAwMlM01m5TGIXNZGUDJQCBctMBjEgWBAuYWC4GF4FC5aYsDEzIMDGAwMlGUxhTQJmzP4wAyUAowAxiTZ9NgsBb0Ci0/gEwARoqABDFcE6BOgTgLQL4vC/F8XYuBacLVF3wtIvhaReF2L4vcXAtfi6L8XvFwX/C1YvcX////+K///iv////////FX//4qKTEFNRTMuMTAwqqqqqqqqqqqqqqqqqqqqqgCEf7VPVL/hACSjSnAGARMAgCAsAygIHcMBMMBYCAwDRTzM+XZMJ4D4weAXQaA0NBSKxl2QgEAwGgG0VDALAIMA4DEIBkLAIIQCWBgTDAXB/BQZBiYIumGQHAYKIEwFAWR+MlcyoTfsRWGWkVECi6voqoqIqKNFa3+o0mygWWmQL9ArwKd/+K8VRUFcE6FWCdQLYA+ivFbiuK/BOgL7xVit+K38Vv8I2Ah/xWioK34J38V/ip+CdQiPFX///4rfFT8C9it//CJwj///CI+EbywAijXor/6E//PkRNcZ0fjqpHstlDT78cwA7pss9AtNkRAKIgWQLMJxcMFwUMZAWMqw5O1ECMgC2MbjVAQqmDYKAQFQMBBgsIxZItOJBYBiHMSxGUSAyKGKQCGMqNGHKEnZaDGeYvGXQpGAoTGFQuiNObIubA6Bx4CVlpywEAoUxw8tKWnLSIFlYcsD/QKTYQKLSoF+gV6bP/6nBYCKNqNKcorqNeit/qNqcwjcI4R+AbwAYeERCP+Eb+Eb/AtY6/4R4RIR/wDd+Ef4RH4BuQLPhE////CN8In8AMIR//4FnAt///As/AtVTEFNRTMuMTAwVVVVVQoqNf6jfor/BqBXlgCAMCxWCxhkKZaQhLQxLBcw7d846DEyPCYxSEEwaCAwgAgwJAkYDECAQXVCgEgwFzBYMzBoOw4pTFYJCEPjBYAjtqUjcoxSAZjEACQYIwN8n2qoXZUSNKC0xpQBaJspsCa0CjKQClTZQKEUDDxFgutEXww+GGC4bEUiLf4XC/xFsReGG4xODLg2DAuuF1uGGxF/4i4ikRTxFIinxFhFfEUiLRFhFBFsLhf/iLcLhoi4XChcJ4ioisRb8LheIrEVEV4YfEX////+IvxF4iv4i2IrFRv/Ua9FXywAQgX8GgYBcrAKMAsC//PkRPIbnfjmVHcRkjhD8cwI92a0YIBiBwZ5glBSGDunoatoeMB2HA6DALGgXcgIBJGsuorAYNAsYLhmDAmMDhBMAQ+MZwkMRyeN4LnBRSkRmCAeTBoFzBsHjBsCAgEi7ZYBpyS0rkoVpspsCIFECjAQBQKAibKBQigYaIsF14i+GGww4XDYikRb/C4f+IviLww3BleDCYNgwLrhdbhhsRb+IuIrEV8RSIr8BJBFPEViLxFhFBF8Lhv/iLcLh4i4XChcJ4igisRb8LhOIpEVEV4YbEX////+IvxF4in4i+IoTEFNRTMuMTAwqqqqqqqqqqqqqqqqqqqqqqqqIEIUIOg6+1ZqjVWqPmzVqqpWrKmEIAKwUY3CxksAmoBudKEJi8EpsBxDMCAUsBcKgpFUKARqxgELICVGCwJwUIgIGYJJg2Fkgw7CUFBGHDCIQKTYKwWMBwWLSFpAIB6BabAJ0CdQTsVBWFSK8VBXiqK0VoqCviqKkVgTj4qgnUVfisEYVeKgrCt8VIqir/yNFWKkE58V+KvhExU+K/wTrgnUVYrxV4qYqRUiv//ivBOoqwTkE7xV+K4rCp/FaK3isKnxUFXitFUXYuYvC94ui7FcXxdi7xdi4LwYkhB0HNnau1Vq//PkROQbPgjqdXOtgjVcEdQy5hskr4Qcra1RUrV1SCEAlwCsUlgAG8aCdfEBi8MIFBxD9RIQABU7V0BBWAWrlYLMLhgwgJCwDCsSnAdcaaOZkoStUR7CpDUU7LRXRVU5UbU4LSlpfLSoFJsoF+mzFQV4qitFaKorYFkCzAtAAf+BZBOoq/FYI4q8VBWFfgW4qRVFX/gIYqxUgnPivxV8IiKnxX+CdcE4ipFaKvFXFWKsVv/8V4J3FWCcAneKnxXFcVf4rxX8VhV+Kgq8V4qC7FzF4XvF0XYrC+LsXeLsXBeqTEFNRTMuMTAwqqqqqqqqqqqqqvTYUb9TlTn0VfUbUaKwEKwLRVCgHBQKTF4ITDVmB5+AqAYQAwUA70VgoAaK6KyKpYAT0VEVDBACzAoHjBcQTGyIRCFpigKIVBgIBYGECJAZERQRUGFhcIIpC4WIuES4MJEX4XWiL4YbiLCK+IpEWEUBgPAaCIuIp4iwrIi2It4i/CKeIqIvkLiKwYjiLwuviKiLxFsRf+IqIoIrEXEV8Il8LhxFRFcRYRQRbCPhHCIhGCICMETwiOEYIgIwRH4RgjcI4R4RGEbwLIFv4R+Eb/hHhGHXhEfCJhHwDfAsmhdYRSIsIvC4SIoIqDEB//PkROwbpfjkAHZtnja78cgIlySQlBcMAnlfmDiicupZx8UBQMhAzCoO9FUKhlFRFVFYsATwgQhAhCgKMFh8yYhzgVdM3l4xAIQoAggKgxAigi4ioigMWFw4isLhoiwRXBiRFuF1oi2GG4XXDD+GHhdcMOQnh0AXXDDeF1hFAutiL+Itx/4igi2DJxFJCcRaF18RQRaIviLfxFBFRFIiwiv/C4QRURTEWEVEWxFsRYRSIuIqIsIrxFeIuIqIsIp+IuIvxFhFoiuIv4YcLr/EW4i//EWiLgyeIr8RSIthcIGHTEFNRTMuMTAwqqqqqqqqqqqqqqqqqqqqqhXCJFTiqCdYqAXQvAj1TkOADSZo2gfEIC1b/EAA1Rq6p1TNV9FVL3w4AMdCzQdIOqTDwBTj4qgnArisBcgnGKgrwEARAqcVBX4q4JyBaitCNFcE7FQC8hHxWisBZFeKsC1FfipxWBvioEcE7FaKnxVFQInFyEcEeFwLUFqxcwjcXeLgWqKkVIq8V4RxWFQVMIgVxVFSKwqeCd4rxVgnAr4rwTuBbFaKvip8VorirBOsE5FSCdYrivFcVxW4rCoKgrCsKwJ1FcC3FaBdQToVATvirBOIrSLiqCdfwjkFcIgVIFiKoJxi//PkROYbdgbmADdtSDVsDcwIbpqQoBdi8CP1OB0cbd6HlmTNW/2StUauqdUzVfRWS89kRhWB8v4dNZOpz8VQTgVxWAuATjFQV5HCJFTioK/FXBOMI0C3COAb4RAAZQLWK8V4rxU4r8VOKwAIAqBGBOxWip8VRUCIxchGBHxcC1BasXMI/F3gK4WqKkVIq8V4RhWFQVMIkVxVFSKwqeCc4rxVgnQrYrwTjFaKnip8VwLQrirBO8E7FWCd4ritFcVxX4riqKgrCuK4JxFbFeBdwToVATvirBOIrQCbFUE6/hGqTEFNRTMuMTAwqqqqqqqqqhKillhjUrRNp5Wx1sjQ297UZM/jWBFOLFqhRjrpiZMe3TygcBhykpKSUQwddlJz8MKkoTipKenjaobfakoVFL6fPWGHN50kYHAYcpEG0EGrTL5Ng1QzBOGjaajAi4LFIHZYQon00y4TAaiRQ0QrTQ0005gXCLgmCDl9N//9SCCASoVJuyFabzAmBzwUJPm76ybBIkEJw0ZNMvhOAtS+X03Ugg1BBCtNPTTegg3gMAiqGCKqyJT/4pR4JCyaYA3mR5TXven973/peGxgblG/j3373v8UpTeKPFYBtUDwDFrFPIWvU8hzj0lk8HtZgqEw//PkZO8bugjkBGYvyrdUCdwSw888fvPMVA9jlZwlr3dQrDPPNyfgyFDAztdPpyf/KaTeUlT3St73SW8pLZ60IS8O2Jff9KHrTRCroE2ebR609C1I4s8lbfwC7F5GWVpGyqKKxXEbKV+UT+/HuRwan/FHhgslN5No9afNz2pk7n53P354jiO15SiC1dAmyY5cH787jPpSm+epsc2Cd6PalIB5GagA0A0FD8AIAH/mGAkx4AANBoKAoCoHHwAvgBf0A0wwaBMYBoGmBCYwKA3wUBIwEjDG1BGCoAJ4AhjKDAVMTEFNRQ5HUklklE67JjcxUjkbElSdIceJRJOASIxicko/El1lgSlYgtqdW4Fh4BQzpCGTgaTZlduIlyVJEiV5EbCp5c8y4cqSsqOUI2EEssmLCMxYeaJRZAsBqEJQOlIKm4YHz1p56FauvWtc9lwcR9CYfmXarXarYvx6bJW1sB9+klYuJKwnXgeOYoDEvKzFglHxVEVsm0v1rxCEcCM2e5aE9TOf1rWmdaXQmJiXh2SsUXVgfJLlrZq3LHSMxPVzZiYuGTzUL1vZdajZdlkxWCU+tr0srYD70okrDE5PSsDZeJKnEFduAhAy5YiFRcDILICbRCOruAtx5luJ0pyE//PkRPscWgjcADEslLjcEbgCS9KQsiilgMzc5KVUt066PIOE3y82o9CWehRWhjnIhMCoamFTyK8WFTAWNCllWAqNERMVYDRcU4kKVEbAJI2bQpskQfAkRDyEiUFTRUlzrIopIpaVMrWQoD6FKeETSwqbimh6ERMEyopSBJGGVhEbFKSZKkiIhSoCQmXCp4VDJ8KkqRNLUK0NSaISWBMmm6VS/jG0NIkQhBkBjSpwTK5cfUcleERohQohkSkT2ZWtHyRNFSXL+gKOhYVIrQokTRUESVIEjbSzRCUExCzWdg1VTEFNRTMuMTAwVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVV"); 
        beep.load();
        let isProcessing = false;

        // Audio Priming to bypass Autoplay Policy
        function primeAudio() {
            beep.play().then(() => {
                beep.pause();
                beep.currentTime = 0;
                console.log("Audio Context Unlocked");
                document.removeEventListener('click', primeAudio);
                document.removeEventListener('touchstart', primeAudio);
            }).catch(e => console.log("Audio prime blocked:", e));
        }
        document.addEventListener('click', primeAudio);
        document.addEventListener('touchstart', primeAudio);

        // Check for Secure Context (HTTPS or Localhost) first
        if (!window.isSecureContext) {
             Swal.fire({
                title: 'Akses Kamera Dibatasi',
                html: `
                    <div class="text-left text-sm text-slate-600">
                        <p class="mb-2">Browser memblokir kamera.</p>
                        <p class="mb-2 font-bold">Pastikan Anda sudah mengaktifkan izin kamera di browser.</p>
                    </div>
                `,
                icon: 'warning',
                confirmButtonText: 'Saya Paham',
                confirmButtonColor: '#6366f1'
            });
        }

        // Global Error Handler for Permissions
        window.addEventListener('unhandledrejection', function(event) {
            if (event.reason && (
                event.reason.toString().includes('NotAllowedError') || 
                event.reason.toString().includes('NotFoundError') ||
                event.reason.toString().includes('NotReadableError')
            )) {
                Swal.fire({
                    title: 'Akses Kamera Bermasalah',
                    html: `
                        <div class="text-left text-sm">
                            <p class="mb-2 font-bold text-rose-600">${event.reason.message || 'Izin ditolak'}</p>
                            <p class="mb-1">Kemungkinan penyebab:</p>
                            <ul class="list-disc pl-5 mb-2">
                                <li>Anda memblokir izin kamera.</li>
                                <li>Web dibuka via <b>HTTP</b> (bukan Localhost/HTTPS).</li>
                                <li>Kamera sedang dipakai aplikasi lain.</li>
                            </ul>
                        </div>
                    `,
                    icon: 'warning'
                });
            }
        });

        // Dynamic QR Box Logic
        function getQrBoxSize() {
            const width = window.innerWidth;
            const size = Math.min(250, width * 0.7); 
            return { width: size, height: size };
        }

        // Audio Play Safety
        async function playBeep() {
            try {
                await beep.play();
            } catch (err) {
                console.log("Audio play blocked (Autoplay policy):", err);
            }
        }

        // Handle Scan Success
        function onScanSuccess(decodedText, decodedResult) {
            if (isProcessing) return;
            isProcessing = true;
            
            playBeep();
            
            processScan(decodedText, false);
        }

        // Separated process function to handle recursive delete confirmation
        function processScan(codeId, confirmDelete = false) {
             // IMMEDIATE FEEDBACK: Show Standard Loader
             if (window.showLoader) window.showLoader();

             const payload = { code_id: codeId };
             if (confirmDelete) payload.confirm_delete = true;

             fetch('<?= base_url('scan/store') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(payload),
                skipLoader: true
            })
            .then(response => response.json())
            .then(data => {
                if (window.hideLoader) window.hideLoader();
                
                if(data.status === 'success') {
                    // Update Stats Immediately
                    updateStats();

                    // DESIGN: Success Save
                    Swal.fire({
                        title: 'Berhasil Masuk!',
                        html: `
                            <div class="mt-2">
                                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3 animate__animated animate__bounceIn">
                                    <i class="fas fa-check text-3xl text-green-500"></i>
                                </div>
                                <h3 class="text-xl font-bold text-slate-800">${data.data.nama}</h3>
                                <p class="text-sm text-slate-500 mb-1">Jimpitan Tercatat</p>
                                <b class="text-2xl text-indigo-600 block my-2">Rp ${new Intl.NumberFormat('id-ID').format(data.data.nominal)}</b>
                            </div>
                        `,
                        showConfirmButton: false,
                        timer: 1000,
                        background: '#fff',
                        customClass: {
                            popup: 'rounded-3xl shadow-xl'
                        }
                    }).then(() => {
                        isProcessing = false;
                    });

                } else if (data.status === 'confirm_delete') {
                    // DESIGN: Confirmation Before Delete
                    Swal.fire({
                        title: 'Data Sudah Ada!',
                        html: `
                            <p class="text-sm text-slate-600 mb-4">Warga ini sudah scan jimpitan hari ini.</p>
                            <div class="bg-slate-50 p-3 rounded-xl border border-slate-100 mb-4">
                                <p class="font-bold text-slate-800">${data.data.nama}</p>
                            </div>
                            <p class="text-sm font-semibold text-rose-600">Apakah ingin MENGHAPUS data ini?</p>
                        `,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Hapus Data',
                        cancelButtonText: 'Batal',
                        confirmButtonColor: '#e11d48',
                        cancelButtonColor: '#94a3b8',
                        reverseButtons: true,
                        customClass: {
                            popup: 'rounded-2xl',
                            confirmButton: 'rounded-xl px-4 py-2 font-bold',
                            cancelButton: 'rounded-xl px-4 py-2 font-bold'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                             // Call again with confirm_delete = true
                             processScan(codeId, true);
                        } else {
                            // Canceled
                            isProcessing = false;
                        }
                    });

                } else if (data.status === 'deleted') {
                    // Update stats immediately
                    updateStats();

                    // DESIGN: Success Delete
                    Swal.fire({
                        title: 'Data Dihapus!',
                        html: `
                            <div class="mt-2">
                                <div class="w-16 h-16 bg-rose-100 rounded-full flex items-center justify-center mx-auto mb-3 animate__animated animate__headShake">
                                    <i class="fas fa-trash-alt text-3xl text-rose-500"></i>
                                </div>
                                <h3 class="text-lg font-bold text-slate-800">${data.data.nama}</h3>
                                <p class="text-sm text-rose-500 mt-1">Transaksi dibatalkan.</p>
                            </div>
                        `,
                        showConfirmButton: false,
                        timer: 1000,
                        customClass: {
                            popup: 'rounded-3xl shadow-xl'
                        }
                    }).then(() => {
                        isProcessing = false;
                    });

                } else {
                    Swal.fire({
                        title: 'Gagal',
                        text: data.message,
                        icon: 'error'
                    }).then(() => {
                        isProcessing = false;
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error');
                isProcessing = false;
            });
        }

        let html5QrCode;
        let isScanning = false;
        let isFlashOn = false;

        async function startScanner() {
            if (isScanning) return;
            if (!html5QrCode) {
                 if (typeof Html5Qrcode === 'undefined') {
                    console.error("Html5Qrcode undefined");
                    return; 
                 }
                 try {
                    html5QrCode = new Html5Qrcode("reader");
                 } catch(e) { console.error("Init error", e); return; }
            }

            const config = { fps: 20, qrbox: 250 };
            
            try {
                // STRATEGY 1: Try Specific "Back" Camera (Best for Flash support on some phones)
                // This mimics the 'original' logic that worked for the user's Flash
                const devices = await Html5Qrcode.getCameras();
                let selectedId = null;
                
                if (devices && devices.length > 0) {
                     for (const device of devices) {
                        const label = device.label.toLowerCase();
                        if (label.includes('back') || label.includes('belakang')) {
                            selectedId = device.id;
                            // Don't break, letting it pick the last one often works better or same as original logic
                        }
                    }
                }

                if (selectedId) {
                    console.log("Attempting Strategy 1: Specific Camera ID", selectedId);
                    await html5QrCode.start(selectedId, config, onScanSuccess, () => {});
                } else {
                    throw new Error("No specific back camera found, fallback to generic.");
                }

            } catch (err1) {
                console.warn("Strategy 1 failed, trying Strategy 2 (Generic)", err1);
                
                try {
                    // STRATEGY 2: Generic Environment (Fallback for compatibility)
                    // Ensure scanner is stopped/cleared if partial fail occurred?
                    // Html5Qrcode usually handles restart ok, but let's just try start.
                    await html5QrCode.start(
                        { facingMode: "environment" }, 
                        config,
                        onScanSuccess, 
                        () => {}
                    );
                } catch (err2) {
                     console.error("All strategies failed", err2);
                     isScanning = false; 
                     document.getElementById('reader').innerHTML = `
                        <div class="p-4 bg-red-50 text-red-600 rounded-lg text-sm font-bold">
                            Gagal Membuka Kamera: ${err2.message}<br>
                            <button onclick="location.reload()" class="mt-2 bg-red-600 text-white px-3 py-1 rounded">Coba Refresh</button>
                        </div>
                    `;
                    return;
                }
            }
            
            // Success (reached if either Strategy 1 or 2 worked)
            isScanning = true;
            document.getElementById('flashToggle').classList.remove('hidden');
            updateFlashUI(); 
        }

        // Init Safely
        document.addEventListener('DOMContentLoaded', () => {
             // Delay slightly to ensure library loaded
             setTimeout(() => {
                 if (typeof Html5Qrcode === 'undefined') {
                     document.getElementById('reader').innerHTML = '<div class="p-4 text-orange-500 font-bold text-sm">Library Scanner Loading... (Cek Koneksi)</div>';
                     // Polling fallback
                     let checkCount = 0;
                     const checker = setInterval(() => {
                         checkCount++;
                         if (typeof Html5Qrcode !== 'undefined') {
                             clearInterval(checker);
                             startScanner();
                         }
                         if(checkCount > 50) { // 5 sec timeout
                             clearInterval(checker);
                             document.getElementById('reader').innerHTML = '<div class="p-4 text-red-500 font-bold text-sm">Gagal memuat Library Scanner. Refresh halaman.</div>';
                         }
                     }, 100);
                 } else {
                     startScanner();
                 }
             }, 100);
        });

        // --- Detail Modal Logic ---
        const detailModal = document.getElementById('detailModal');
        // Clean up duplicates if any below...
        const detailList = document.getElementById('detailList');
        const detailCountSpan = document.getElementById('detailCount');
        const totalScanDisplay = document.getElementById('totalScanDisplay');

        function openDetailModal() {
            // Push State
            history.pushState({ modal: 'detail' }, '', '#detail');
            
            detailModal.classList.remove('invisible', 'opacity-0');
            const content = detailModal.querySelector('div.transform');
            content.classList.remove('scale-95', 'opacity-0');
            content.classList.add('scale-100', 'opacity-100'); 
            loadDetails(); 
        }

        // Internal UI Close Logic
        function _closeDetailUI() {
            const content = detailModal.querySelector('div.transform');
            content.classList.remove('scale-100', 'opacity-100');
            content.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                detailModal.classList.add('opacity-0', 'invisible');
            }, 300);
        }

        // Triggered by User Action (Button/Backdrop)
        function closeDetailModal() {
            // If we have history state, go back (triggering popstate)
            // If not (direct load?), just close UI
            if (location.hash === '#detail') {
                history.back();
            } else {
                _closeDetailUI();
            }
        }

        // Handle Back Button
        window.addEventListener('popstate', (event) => {
            // If URL no longer has #detail or custom state, close UI
            _closeDetailUI();
        });

        function refreshDetails() {
            // Animate Icon
            const icon = detailModal.querySelector('.fa-sync-alt');
            icon.classList.add('fa-spin');
            loadDetails().then(() => {
                setTimeout(() => icon.classList.remove('fa-spin'), 500);
            });
        }

        async function loadDetails(silent = false) {
            try {
                // Assuming Scan::getRecentScans is mapped to /scan/getRecentScans or similar
                // CodeIgniter 4 controller/method routing is often auto-mapped or configured in Routes.php
                // I will use scan/getRecentScans based on default convention
                const response = await fetch('<?= base_url('scan/getRecentScans') ?>?t=' + new Date().getTime(), { skipLoader: silent }); 
                const data = await response.json();
                
                if(data.status === 'success') {
                    renderDetails(data.data);
                    // Update stats string
                    const formattedTotal = new Intl.NumberFormat('id-ID').format(data.total_nominal || 0);
                    const statsString = `${data.count} KK - Rp ${formattedTotal}`;
                    
                    totalScanDisplay.innerText = statsString;
                    detailCountSpan.innerText = statsString;
                }
            } catch(e) {
                console.error(e);
                if (!silent) {
                    detailList.innerHTML = '<p class="text-center text-red-500 py-4">Gagal memuat data.</p>';
                }
            }
        }

        function renderDetails(items) {
            const list = document.getElementById('detailList');
            
            if(!items || items.length === 0) {
                list.innerHTML = `
                    <div class="text-center py-8 opacity-50">
                        <i class="fas fa-inbox text-3xl mb-2 text-slate-300"></i>
                        <p class="text-[10px]">Belum ada data hari ini.</p>
                    </div>
                `;
                return;
            }

            // Remove empty state placeholder
            if(list.querySelector('.fa-inbox')) list.innerHTML = '';

            const existingMap = new Map();
            list.querySelectorAll('.scan-item').forEach(el => existingMap.set(el.dataset.id, el));
            const processedIds = new Set();

            items.forEach((item, index) => {
                const itemId = item.id ? String(item.id) : `${item.nama}-${item.waktu}`;
                processedIds.add(itemId);

                let el = existingMap.get(itemId);
                // Numbering: Index + 1
                const number = index + 1;

                if (!el) {
                    el = document.createElement('div');
                    // Ultra Compact: p-1, text-xs, border-b only (no margin between items for table look)
                    el.className = 'scan-item bg-white dark:bg-slate-800 px-2 py-1.5 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center animate__animated animate__fadeIn first:rounded-t-lg last:border-0 last:rounded-b-lg hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors';
                    el.dataset.id = itemId;
                    updateElementContent(el, item, number);
                } else {
                    updateElementContent(el, item, number);
                }
                list.appendChild(el);
            });

            existingMap.forEach((el, id) => {
                if (!processedIds.has(id)) el.remove();
            });
        }

        function updateElementContent(el, item, number) {
            const isSystem = (item.collector === 'System' || item.collector.toLowerCase() === 'system');
            
            const badgeItem = isSystem 
                ? `<span class="px-1 py-0.5 rounded text-[8px] font-bold bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400 ml-1 uppercase leading-none border border-amber-200 dark:border-amber-800/50 inline-block">Manual/Sys</span>`
                : '';

            el.innerHTML = `
                <div class="flex gap-2 items-center min-w-0">
                    <div class="w-5 h-5 rounded-md bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400 flex items-center justify-center text-[10px] font-bold font-mono shrink-0">
                        ${number}
                    </div>
                    <div class="min-w-0 truncate">
                        <div class="flex items-center">
                            <p class="text-xs font-bold text-slate-800 dark:text-white leading-none truncate">${item.nama}</p>
                            ${badgeItem}
                        </div>
                        <p class="text-[9px] ${isSystem ? 'text-amber-500 font-medium' : 'text-slate-400 dark:text-slate-500'} leading-none mt-1 truncate">
                           ${item.collector}
                        </p>
                    </div>
                </div>
                <div class="text-right shrink-0 pl-2">
                    <p class="text-xs font-bold text-indigo-600 dark:text-indigo-400 leading-none">Rp ${new Intl.NumberFormat('id-ID').format(item.nominal)}</p>
                    <span class="text-[9px] text-slate-400 font-mono mt-0.5 block">${item.waktu}</span>
                </div>
            `;
        }

        function updateStats() {
            // Re-fetch everything
             loadDetails(true);
        }

        // Realtime Polling (Every 5 seconds)
        setInterval(() => {
            loadDetails(true);
        }, 3000);

        // Flash/Torch Control
        // Removed checkFlashCapability to force button show
        
        async function toggleFlash() {
            let success = false;
            const targetState = !isFlashOn;

            // Method 1: Html5Qrcode Helper
            if (html5QrCode) {
                try {
                    await html5QrCode.applyVideoConstraints({ advanced: [{ torch: targetState }] });
                    success = true;
                } catch (err) {
                    console.log("Method 1 (Lib) failed:", err);
                }
            }

            // Method 2: Native Track Manipulation (Stronger)
            if (!success) {
                try {
                    const video = document.querySelector('#reader video');
                    if (video && video.srcObject) {
                        const track = video.srcObject.getVideoTracks()[0];
                        
                        // Force constraints
                        await track.applyConstraints({
                            advanced: [{ torch: targetState }]
                        });
                        success = true;
                    }
                } catch (err) {
                     console.log("Method 2 (Native) failed:", err);
                     // Method 3: Fallback for some weird devices (brightness/fillLight)
                     try {
                        const video = document.querySelector('#reader video');
                        const track = video.srcObject.getVideoTracks()[0];
                        await track.applyConstraints({
                            advanced: [{ fillLightMode: targetState ? "flash" : "off" }]
                        });
                        success = true;
                     } catch(e) {}
                }
            }

            if (success) {
                isFlashOn = targetState;
                updateFlashUI();
            } else {
                // If it fails, force UI update anyway to 'pretend' but warn execution failed?
                // No, better to show error.
                Swal.fire({
                    toast: true,
                    position: 'top',
                    icon: 'error',
                    title: 'Gagal menyalakan lampu.',
                    text: 'Gagal akses hardware. Coba refresh halaman.',
                    showConfirmButton: false,
                    timer: 2000
                });
            }
        }

        function updateFlashUI() {
            const btn = document.getElementById('flashToggle');
            const icon = btn.querySelector('i');
            
            // Clean slate for icon to avoid class conflict
            icon.className = 'fas fa-bolt text-lg transition-colors duration-300';

            if (isFlashOn) {
                // State: ON
                btn.classList.remove('flash-off', 'animate-heartbeat'); 
                btn.classList.add('flash-on');
                
                // Icon: White, Pulsing
                icon.classList.add('text-white', 'animate-pulse');
            } else {
                // State: OFF
                btn.classList.remove('flash-on');
                btn.classList.add('flash-off', 'animate-heartbeat'); 
                
                // Icon: Yellow, Also Pulsing (to match button heartbeat)
                icon.classList.add('text-yellow-400', 'animate-pulse');
            }
        }

        // Realtime Clock
        function updateClock() {
            const now = new Date();
            const optionsDate = { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' };
            const dateStr = now.toLocaleDateString('id-ID', optionsDate);
            const timeStr = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }).replace(/\./g, ':');
            
            // Side by side layout
            document.getElementById('headerClock').innerHTML = `${dateStr} &bull; ${timeStr}`;
        }
        setInterval(updateClock, 1000);
        updateClock(); // Init
    </script>

    <!-- Global Loader -->
    <?= $this->include('partials/loader') ?>

</body>
</html>
