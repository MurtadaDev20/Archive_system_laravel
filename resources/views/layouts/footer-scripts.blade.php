<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
@vite(['resources/js/app.js'])
@yield('js')
@livewireScripts

<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (window.toastr) {
            toastr.options = {
                closeButton: true,
                progressBar: true,
                positionClass: 'toast-top-left',
                timeOut: 6000,
                extendedTimeOut: 2000,
                rtl: true,
            };
        }

        const preloader = document.getElementById('pre-loader');
        if (preloader) {
            preloader.classList.add('hidden');
            setTimeout(() => preloader.remove(), 400);
        }

        const sidebar = document.getElementById('archiveSidebar');
        const backdrop = document.getElementById('sidebarBackdrop');
        const toggle = document.getElementById('sidebarToggle');

        function closeSidebar() {
            sidebar?.classList.remove('show');
            backdrop?.classList.remove('show');
        }

        toggle?.addEventListener('click', function () {
            sidebar?.classList.toggle('show');
            backdrop?.classList.toggle('show');
        });

        backdrop?.addEventListener('click', closeSidebar);

        const themeToggle = document.getElementById('themeToggle');
        const savedTheme = localStorage.getItem('archive-theme') || 'light';
        document.documentElement.setAttribute('data-bs-theme', savedTheme);
        themeToggle?.addEventListener('click', function () {
            const next = document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-bs-theme', next);
            localStorage.setItem('archive-theme', next);
        });
    });

    document.addEventListener('livewire:init', function () {
        Livewire.on('archive-notify', function (payload) {
            const data = payload && typeof payload === 'object' && !Array.isArray(payload) ? payload : (Array.isArray(payload) ? payload[0] : {});
            const type = data.type || 'info';
            const message = data.message || '';
            if (!window.toastr || !message) {
                return;
            }
            if (typeof toastr[type] === 'function') {
                toastr[type](message);
            } else {
                toastr.info(message);
            }
        });

        Livewire.on('sidebar-counts-updated', function (payload) {
            const data = payload && typeof payload === 'object' && !Array.isArray(payload) ? payload : (Array.isArray(payload) ? payload[0] : {});
            const counts = data.counts || {};
            const badge = document.getElementById('sidebar-documents-badge');
            if (!badge) {
                return;
            }
            const total = Number(counts.documents || 0);
            badge.textContent = total > 99 ? '99+' : String(total);
            badge.classList.toggle('d-none', total <= 0);
        });

        Livewire.on('archive-refreshed', function () {
            document.querySelectorAll('[wire\\:id]').forEach(function (el) {
                const component = Livewire.find(el.getAttribute('wire:id'));
                const name = component?.name ?? '';
                if (name === 'manage-file-livewire' || name === 'document-detail-livewire') {
                    component.$wire.$refresh();
                }
            });
        });

        if (typeof Echo !== 'undefined') {
            const currentUserId = document.querySelector('meta[name="user-id"]')?.content;
            const teamManagerId = document.querySelector('meta[name="team-manager-id"]')?.content;

            function handleArchiveActivity(event) {
                const excludeActorId = event.exclude_actor_id ? String(event.exclude_actor_id) : null;
                if (excludeActorId && currentUserId && excludeActorId === String(currentUserId)) {
                    Livewire.dispatch('archive-refreshed');
                    return;
                }

                const targets = Array.isArray(event.target_user_ids) ? event.target_user_ids.map(String) : [];
                const shouldNotify = targets.length === 0
                    || (currentUserId && targets.includes(String(currentUserId)));

                if (shouldNotify && event.message) {
                    Livewire.dispatch('archive-notify', { type: event.type || 'info', message: event.message });
                }

                Livewire.dispatch('archive-refreshed');
            }

            if (teamManagerId) {
                Echo.private('team.' + teamManagerId).listen('.ArchiveActivity', handleArchiveActivity);
            }

            // احتياط: إذا WebSocket غير متصل، فعّل polling خفيف كل 45 ثانية
            const pollHost = document.querySelector('[data-archive-realtime-poll="0"]');
            if (pollHost && typeof Echo !== 'undefined' && Echo.connector?.pusher) {
                const fallbackPollMs = 45000;
                let fallbackTimer = null;

                function startFallbackPoll() {
                    if (fallbackTimer) return;
                    fallbackTimer = setInterval(function () {
                        const el = document.querySelector('[data-archive-realtime-poll="0"]');
                        const wireId = el?.getAttribute('wire:id');
                        const component = wireId ? Livewire.find(wireId) : null;
                        if (component) {
                            component.$wire.sync();
                        }
                    }, fallbackPollMs);
                }

                function stopFallbackPoll() {
                    if (fallbackTimer) {
                        clearInterval(fallbackTimer);
                        fallbackTimer = null;
                    }
                }

                Echo.connector.pusher.connection.bind('connected', stopFallbackPoll);
                Echo.connector.pusher.connection.bind('disconnected', startFallbackPoll);
                Echo.connector.pusher.connection.bind('unavailable', startFallbackPoll);

                if (Echo.connector.pusher.connection.state !== 'connected') {
                    startFallbackPoll();
                }
            }
        }
    });
</script>
