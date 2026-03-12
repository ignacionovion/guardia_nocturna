    <script>
        // Auto-enable fullscreen for guardia users on page load
        @if(Auth::check() && Auth::user()->role === 'guardia')
        (function() {
            function tryFullscreen() {
                if (!document.fullscreenElement && !sessionStorage.getItem('fullscreenAutoEnabled')) {
                    try {
                        document.documentElement.requestFullscreen().then(function() {
                            sessionStorage.setItem('fullscreenAutoEnabled', 'true');
                            console.log('Fullscreen auto-enabled');
                        }).catch(function(err) {
                            console.log('Fullscreen auto-enable failed (will retry on interaction):', err);
                            // Fallback: try on first user interaction
                            enableFullscreenOnInteraction();
                        });
                    } catch (e) {
                        console.log('Fullscreen error:', e);
                        enableFullscreenOnInteraction();
                    }
                }
            }

            function enableFullscreenOnInteraction() {
                var events = ['click', 'touchstart', 'keydown', 'mousemove'];
                var handler = function() {
                    if (!document.fullscreenElement && !sessionStorage.getItem('fullscreenAutoEnabled')) {
                        try {
                            document.documentElement.requestFullscreen().then(function() {
                                sessionStorage.setItem('fullscreenAutoEnabled', 'true');
                                console.log('Fullscreen enabled on interaction');
                            }).catch(function() {
                                // Silent fail
                            });
                        } catch (e) {
                            // Silent fail
                        }
                    }
                    // Remove all listeners after first attempt
                    events.forEach(function(evt) {
                        document.removeEventListener(evt, handler);
                    });
                };
                events.forEach(function(evt) {
                    document.addEventListener(evt, handler, { once: true });
                });
            }

            // Try immediately if document is already ready
            if (document.readyState === 'complete' || document.readyState === 'interactive') {
                setTimeout(tryFullscreen, 100);
            } else {
                document.addEventListener('DOMContentLoaded', function() {
                    setTimeout(tryFullscreen, 100);
                });
            }
        })();
        @endif

        function toggleFullscreen() {
            try {
                if (!document.fullscreenElement) {
                    document.documentElement.requestFullscreen();
                    sessionStorage.setItem('fullscreenAutoEnabled', 'true');
                } else {
                    document.exitFullscreen();
                    sessionStorage.removeItem('fullscreenAutoEnabled');
                }
            } catch (e) {
                // No-op
            }
        }

        // Custom Replacement Dropdown
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('replacement-search-input');
            const dropdown = document.getElementById('replacement-dropdown');
            const filterInput = document.getElementById('replacement-filter-input');
            const optionsList = document.getElementById('replacement-options-list');
            const noResults = document.getElementById('replacement-no-results');
            const hiddenInput = document.getElementById('modal_replacement_firefighter_id');
            const container = document.getElementById('replacement-select-container');
            
            if (!searchInput || !dropdown) return;

            let isOpen = false;

            // Toggle dropdown
            searchInput.addEventListener('click', function(e) {
                e.stopPropagation();
                if (!isOpen) {
                    openDropdown();
                }
            });

            function openDropdown() {
                isOpen = true;
                dropdown.classList.remove('hidden');
                filterInput.focus();
                filterInput.value = '';
                filterOptions('');
            }

            function closeDropdown() {
                isOpen = false;
                dropdown.classList.add('hidden');
            }

            // Close on click outside
            document.addEventListener('click', function(e) {
                if (!container.contains(e.target)) {
                    closeDropdown();
                }
            });

            // Filter functionality
            if (filterInput) {
                filterInput.addEventListener('input', function() {
                    filterOptions(this.value.toLowerCase());
                });
            }

            function filterOptions(query) {
                const options = optionsList.querySelectorAll('.replacement-option');
                let visibleCount = 0;

                options.forEach(function(option) {
                    const searchData = option.getAttribute('data-search') || '';
                    if (searchData.includes(query)) {
                        option.classList.remove('hidden');
                        visibleCount++;
                    } else {
                        option.classList.add('hidden');
                    }
                });

                if (visibleCount === 0) {
                    noResults.classList.remove('hidden');
                } else {
                    noResults.classList.add('hidden');
                }
            }

            // Option selection
            optionsList.addEventListener('click', function(e) {
                const option = e.target.closest('.replacement-option');
                if (!option) return;

                const value = option.getAttribute('data-value');
                const text = option.querySelector('.text-sm').textContent.trim();

                hiddenInput.value = value;
                searchInput.value = text;
                closeDropdown();
            });

            // Keyboard navigation
            filterInput.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeDropdown();
                    searchInput.focus();
                }
            });
        });

        // Custom Refuerzo Dropdown
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('refuerzo-search-input');
            const dropdown = document.getElementById('refuerzo-dropdown');
            const filterInput = document.getElementById('refuerzo-filter-input');
            const optionsList = document.getElementById('refuerzo-options-list');
            const noResults = document.getElementById('refuerzo-no-results');
            const hiddenInput = document.getElementById('refuerzo_firefighter_id');
            const container = document.getElementById('refuerzo-select-container');
            
            if (!searchInput || !dropdown) return;

            let isOpen = false;

            // Toggle dropdown
            searchInput.addEventListener('click', function(e) {
                e.stopPropagation();
                if (!isOpen) {
                    openDropdown();
                }
            });

            function openDropdown() {
                isOpen = true;
                dropdown.classList.remove('hidden');
                filterInput.focus();
                filterInput.value = '';
                filterOptions('');
            }

            function closeDropdown() {
                isOpen = false;
                dropdown.classList.add('hidden');
            }

            // Close on click outside
            document.addEventListener('click', function(e) {
                if (!container.contains(e.target)) {
                    closeDropdown();
                }
            });

            // Filter functionality
            if (filterInput) {
                filterInput.addEventListener('input', function() {
                    filterOptions(this.value.toLowerCase());
                });
            }

            function filterOptions(query) {
                const options = optionsList.querySelectorAll('.refuerzo-option');
                let visibleCount = 0;

                options.forEach(function(option) {
                    const searchData = option.getAttribute('data-search') || '';
                    if (searchData.includes(query)) {
                        option.classList.remove('hidden');
                        visibleCount++;
                    } else {
                        option.classList.add('hidden');
                    }
                });

                if (visibleCount === 0) {
                    noResults.classList.remove('hidden');
                } else {
                    noResults.classList.add('hidden');
                }
            }

            // Option selection
            optionsList.addEventListener('click', function(e) {
                const option = e.target.closest('.refuerzo-option');
                if (!option) return;

                const value = option.getAttribute('data-value');
                const text = option.querySelector('.text-sm').textContent.trim();

                hiddenInput.value = value;
                searchInput.value = text;
                closeDropdown();
            });

            // Keyboard navigation
            filterInput.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeDropdown();
                    searchInput.focus();
                }
            });

            // Form validation
            const refuerzoForm = searchInput.closest('form');
            if (refuerzoForm) {
                refuerzoForm.addEventListener('submit', (e) => {
                    if ((hiddenInput.value || '').trim() === '') {
                        e.preventDefault();
                        alert('Debes seleccionar un voluntario.');
                    }
                });
            }
        });

        // Send bed report email function
        window.sendBedReportEmail = async function() {
            const btn = event.target.closest('button');
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            btn.disabled = true;
            
            try {
                const response = await fetch('{{ route("camas.report.email") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    credentials: 'same-origin',
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    btn.innerHTML = '<i class="fas fa-check text-emerald-400"></i>';
                    setTimeout(() => {
                        btn.innerHTML = originalHtml;
                        btn.disabled = false;
                    }, 2000);
                } else {
                    throw new Error(data.message || 'Error al enviar el reporte');
                }
            } catch (e) {
                alert(e.message || 'Error al enviar el reporte');
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            }
        }
    </script>
