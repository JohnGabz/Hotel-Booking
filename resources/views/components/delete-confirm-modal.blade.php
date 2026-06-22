<x-modal id="delete-confirm-modal" title="Delete Item?" size="max-w-md">
    <form id="delete-confirm-form" method="POST" action="#">
        @csrf
        <p class="text-sm leading-relaxed text-stone-600">
            Are you sure you want to delete this item?
            <span class="mt-2 block font-medium text-stone-800">This action cannot be undone.</span>
        </p>
        <div class="mt-5 flex items-center justify-end gap-3">
            <button type="button" data-modal-close class="btn-secondary">Cancel</button>
            <button type="submit" class="btn-primary bg-red-600 hover:bg-red-700 border-red-600">Delete</button>
        </div>
    </form>
</x-modal>
