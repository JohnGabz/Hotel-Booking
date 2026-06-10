<script>
    window.initImageInputToggles = function (root) {
        const scope = root || document;

        scope.querySelectorAll('[data-image-input]').forEach((container) => {
            if (container.getAttribute('data-toggle-ready') === '1') {
                return;
            }
            container.setAttribute('data-toggle-ready', '1');

            const modeInput = container.querySelector('.image-input-mode');
            const uploadPanel = container.querySelector('.image-upload-panel');
            const urlPanel = container.querySelector('.image-url-panel');
            const fileInput = container.querySelector('input[type="file"]');
            const urlInput = container.querySelector('input[type="url"]');
            const linksInput = container.querySelector('textarea[name="image_links"]');

            const setMode = (mode) => {
                if (modeInput) {
                    modeInput.value = mode;
                }

                container.querySelectorAll('.image-mode-btn').forEach((btn) => {
                    const active = btn.getAttribute('data-mode') === mode;
                    btn.classList.toggle('bg-white', active);
                    btn.classList.toggle('text-stone-900', active);
                    btn.classList.toggle('shadow-sm', active);
                    btn.classList.toggle('text-stone-500', !active);
                });

                uploadPanel?.classList.toggle('hidden', mode !== 'upload');
                urlPanel?.classList.toggle('hidden', mode !== 'url');
            };

            container.querySelectorAll('.image-mode-btn').forEach((btn) => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    setMode(btn.getAttribute('data-mode'));
                });
            });

            setMode(modeInput?.value || 'upload');

            const form = container.closest('form');
            if (form && !form.getAttribute('data-image-validated')) {
                form.setAttribute('data-image-validated', '1');
                form.addEventListener('submit', (e) => {
                    form.querySelectorAll('[data-image-input]').forEach((toggle) => {
                        const mode = toggle.querySelector('.image-input-mode')?.value || 'upload';
                        const fileInput = toggle.querySelector('input[type="file"]');
                        const urlInput = toggle.querySelector('input[type="url"]');
                        const linksInput = toggle.querySelector('textarea[name="image_links"]');

                        if (mode === 'upload') {
                            if (urlInput) urlInput.value = '';
                            if (linksInput) linksInput.value = '';
                        } else if (fileInput) {
                            fileInput.value = '';
                        }
                    });

                    const toggles = form.querySelectorAll('[data-image-input]');
                    toggles.forEach((toggle) => {
                        const mode = toggle.querySelector('.image-input-mode')?.value || 'upload';
                        const files = toggle.querySelector('input[type="file"]')?.files;

                        if (mode === 'upload' && files && files.length > 0) {
                            let totalSize = 0;
                            for (let i = 0; i < files.length; i++) {
                                if (files[i].size > 10 * 1024 * 1024) {
                                    e.preventDefault();
                                    alert('Each image file must be 10MB or smaller.');
                                    return;
                                }
                                totalSize += files[i].size;
                            }
                            if (totalSize > 50 * 1024 * 1024) {
                                e.preventDefault();
                                alert('Total upload size must be 50MB or smaller.');
                                return;
                            }
                        }
                    });
                });
            }
        });
    };

    document.addEventListener('DOMContentLoaded', () => {
        window.initImageInputToggles();
    });
</script>
