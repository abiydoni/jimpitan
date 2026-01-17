
<!-- Global Loader Component -->
<div id="global-loader" class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-50/80 dark:bg-slate-900/80 backdrop-blur-sm transition-opacity duration-300 opacity-0 pointer-events-none">
    <div class="relative flex flex-col items-center">
        <!-- Spinner Animation -->
        <div class="relative w-16 h-16 sm:w-20 sm:h-20">
            <div class="absolute inset-0 rounded-full border-4 border-slate-200 dark:border-slate-700"></div>
            <div class="absolute inset-0 rounded-full border-4 border-indigo-600 border-t-transparent animate-spin"></div>
            
            <!-- Logo/Icon in center -->
            <div class="absolute inset-0 flex items-center justify-center">
                <i class="fas fa-bolt text-indigo-600 dark:text-indigo-400 text-xl sm:text-2xl animate-pulse"></i>
            </div>
        </div>
        
        <p class="mt-4 text-sm font-semibold text-slate-600 dark:text-slate-300 animate-pulse tracking-wide">
            Memproses...
        </p>
    </div>
</div>

<script>
    (function() {
        const loader = document.getElementById('global-loader');
        let activeRequests = 0;

        // Public API
        window.showLoader = function() {
            if (loader) {
                loader.classList.remove('opacity-0', 'pointer-events-none');
            }
        };

        window.hideLoader = function() {
            if (loader) {
                loader.classList.add('opacity-0', 'pointer-events-none');
            }
        };

        // 1. Page Navigation (Show on link click or form submit)
        // Note: We use 'beforeunload' as a fallback, but sometimes it doesn't render quickly enough.
        // Adding listeners to links is more instant.
        document.addEventListener('click', (e) => {
            const anchor = e.target.closest('a');
            if (anchor && anchor.href && !anchor.href.startsWith('javascript:') && !anchor.href.includes('#') && anchor.target !== '_blank') {
                // Check if it's a download link or same page anchor
                const url = new URL(anchor.href, window.location.href);
                if (url.origin === window.location.origin && url.pathname !== window.location.pathname) {
                    window.showLoader();
                }
            }
        });

        document.addEventListener('submit', (e) => {
            if (!e.defaultPrevented) {
                window.showLoader();
            }
        });

        // 2. Hide on Page Load (Safe guard)
        window.addEventListener('load', () => {
             // Small delay to ensure smooth transition if cache served effectively
            setTimeout(() => {
                window.hideLoader();
            }, 300);
        });
        
        // Also hide immediately if the page is restored from bfcache (Back/Forward cache)
        window.addEventListener('pageshow', (event) => {
            if (event.persisted) {
                window.hideLoader();
            }
        });

        // 3. Fetch/XHR Interceptor
        const originalFetch = window.fetch;
        window.fetch = async function(...args) {
            // Check if 'skipLoader' is passed in options (2nd arg)
            const options = args[1];
            const shouldSkip = options && options.skipLoader === true;

            if (!shouldSkip) {
                activeRequests++;
                window.showLoader();
            }
            
            try {
                const response = await originalFetch(...args);
                return response;
            } finally {
                if (!shouldSkip) {
                    activeRequests--;
                    if (activeRequests === 0) {
                        window.hideLoader();
                    }
                }
            }
        };

    })();
</script>
