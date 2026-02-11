<script>
    document.addEventListener('DOMContentLoaded', () => {
        const forms = document.querySelectorAll('form');
        
        forms.forEach(form => {
            // Check if form already has a specific onsubmit handler that might conflict
            // But usually we can add our listener as well.
            
            form.addEventListener('submit', function(e) {
                const btn = form.querySelector('button[type="submit"]');
                
                if (btn && !btn.disabled) {
                    // Store original HTML if not already stored
                    if (!btn.dataset.originalHtml) {
                        btn.dataset.originalHtml = btn.innerHTML;
                    }
                    
                    // Disable it
                    btn.disabled = true;
                    btn.classList.add('opacity-75', 'cursor-not-allowed');
                    
                    // Add spinner or replace icon
                    const icon = btn.querySelector('i');
                    if(icon) {
                        icon.dataset.originalClass = icon.className;
                        icon.className = 'fas fa-spinner fa-spin mr-1';
                    } else {
                        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Memproses...';
                    }
                }
            });
        });

        // Expose a helper to re-enable global buttons if needed (for AJAX error handlers)
        window.resetSubmitButtons = () => {
            document.querySelectorAll('button[type="submit"]').forEach(btn => {
                btn.disabled = false;
                btn.classList.remove('opacity-75', 'cursor-not-allowed');
                
                // Restore original HTML if stored
                if (btn.dataset.originalHtml) {
                    btn.innerHTML = btn.dataset.originalHtml;
                    delete btn.dataset.originalHtml;
                }

                // Extra safety: restore icon class if manually modified but HTML same
                const icon = btn.querySelector('i');
                if (icon && icon.dataset.originalClass) {
                    icon.className = icon.dataset.originalClass;
                    delete icon.dataset.originalClass;
                }
            });
        };
    });
</script>
