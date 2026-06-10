<script>
    (function () {
        const contentDiv = document.getElementById('dashboard-section-content');
        const navLinks = document.querySelectorAll('[data-dashboard-section]');
        if (!contentDiv) return;

        const executeScripts = (container) => {
            container.querySelectorAll('script').forEach((oldScript) => {
                const script = document.createElement('script');
                Array.from(oldScript.attributes).forEach((attr) => script.setAttribute(attr.name, attr.value));
                script.textContent = oldScript.textContent;
                oldScript.replaceWith(script);
            });
        };

        const loadSection = async (section, pushState = true) => {
            const url = new URL(window.location.href);
            url.searchParams.set('section', section);

            contentDiv.innerHTML = '<div class="p-8 text-center text-stone-500">Loading...</div>';

            try {
                const response = await fetch(url.toString(), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' },
                });

                if (!response.ok) throw new Error('Failed to load section');

                const html = await response.text();
                contentDiv.innerHTML = html;
                executeScripts(contentDiv);

                if (typeof window.initImageInputToggles === 'function') {
                    window.initImageInputToggles(contentDiv);
                }
                if (typeof window.initRoomTypeModal === 'function') {
                    window.initRoomTypeModal(contentDiv);
                }

                navLinks.forEach((link) => {
                    link.classList.toggle('nav-link-active', link.dataset.dashboardSection === section);
                });

                if (pushState) {
                    history.pushState({ section }, '', url.toString());
                }
            } catch (error) {
                contentDiv.innerHTML = '<div class="p-8 text-center text-red-600">Could not load this section. Please refresh.</div>';
            }
        };

        navLinks.forEach((link) => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                loadSection(link.dataset.dashboardSection);
            });
        });

        window.addEventListener('popstate', (e) => {
            const section = e.state?.section || new URL(window.location.href).searchParams.get('section') || 'dashboard';
            loadSection(section, false);
        });

        const initialSection = new URL(window.location.href).searchParams.get('section');
        if (initialSection && initialSection !== 'dashboard') {
            loadSection(initialSection, false);
        }
    })();
</script>
