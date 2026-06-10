<script>
    window.initRoomTypeModal = function (root) {
        const scope = root || document;
        const modal = scope.matches?.('#add-room-modal')
            ? scope
            : scope.querySelector('#add-room-modal') || document.getElementById('add-room-modal');

        if (!modal) {
            return;
        }

        const container = modal.querySelector('#add-physical-units-container');
        const addBtn = modal.querySelector('#add-physical-unit-row');

        if (!container || !addBtn) {
            return;
        }

        if (addBtn.getAttribute('data-unit-bound') === '1') {
            return;
        }
        addBtn.setAttribute('data-unit-bound', '1');

        let unitIndex = container.querySelectorAll('[data-physical-unit-row]').length;

        const addRow = (name = '', code = '', available = true) => {
            const row = document.createElement('div');
            row.setAttribute('data-physical-unit-row', '1');
            row.className = 'grid gap-2 sm:grid-cols-4 items-end border border-stone-200 rounded-lg p-3';
            row.innerHTML = `
                <div>
                    <label class="text-xs text-stone-600">Unit name</label>
                    <input type="text" name="physical_rooms[${unitIndex}][name]" value="${name.replace(/"/g, '&quot;')}" placeholder="e.g. Room 101" class="form-input py-1.5 px-3 text-xs w-full border border-gray-300">
                </div>
                <div>
                    <label class="text-xs text-stone-600">Unit code</label>
                    <input type="text" name="physical_rooms[${unitIndex}][code]" value="${code.replace(/"/g, '&quot;')}" placeholder="e.g. room-101" class="form-input py-1.5 px-3 text-xs w-full border border-gray-300">
                </div>
                <div class="flex items-center gap-2 pt-5">
                    <label class="flex items-center gap-1 text-sm text-stone-700">
                        <input type="checkbox" name="physical_rooms[${unitIndex}][is_available]" value="1" ${available ? 'checked' : ''} class="rounded border-stone-300">
                        Available
                    </label>
                </div>
                <div class="pt-5 text-right">
                    <button type="button" class="remove-unit-row text-xs font-semibold text-red-600 hover:underline">Remove</button>
                </div>
            `;

            row.querySelector('.remove-unit-row').addEventListener('click', (e) => {
                e.preventDefault();
                row.remove();
            });

            container.appendChild(row);
            unitIndex++;
        };

        addBtn.addEventListener('click', (e) => {
            e.preventDefault();
            addRow();
        });

        if (container.children.length === 0 && addBtn.getAttribute('data-seeded') !== '1') {
            addBtn.setAttribute('data-seeded', '1');
        }
    };

    document.addEventListener('DOMContentLoaded', () => {
        window.initRoomTypeModal();

        @if (old('name') && $errors->any())
            const addModal = document.getElementById('add-room-modal');
            if (addModal && typeof openModalById === 'function') {
                openModalById('add-room-modal');
            } else if (addModal && typeof openAccessibleModal === 'function') {
                openAccessibleModal(addModal);
            }
            if (typeof window.initImageInputToggles === 'function') {
                window.initImageInputToggles(addModal);
            }
            window.initRoomTypeModal(addModal);
        @endif
    });
</script>
