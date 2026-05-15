@include('errors._page', [
    'status' => $status ?? 419,
    'title' => $title ?? 'Your session expired',
    'message' => $message ?? 'For your safety, this form expired. Please refresh the page and try again.',
    'action' => 'Start again',
])
