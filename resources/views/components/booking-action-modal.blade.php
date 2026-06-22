<x-modal id="booking-action-modal" title="Confirm action" size="max-w-md">
    <form id="booking-action-form" method="POST" action="#">
        @csrf
        <p id="booking-action-message" class="text-sm leading-relaxed text-stone-600"></p>
        <div class="mt-4 form-group">
            <label class="form-label" for="booking-action-reason">Reason (optional)</label>
            <textarea id="booking-action-reason" name="cancellation_reason" rows="3" maxlength="500" class="form-input" placeholder="Tell us why you are cancelling or requesting a refund"></textarea>
        </div>
        <div class="mt-5 flex items-center justify-end gap-3">
            <button type="button" data-modal-close class="btn-secondary">Cancel</button>
            <button type="submit" id="booking-action-submit" class="btn-primary">Confirm</button>
        </div>
    </form>
</x-modal>
