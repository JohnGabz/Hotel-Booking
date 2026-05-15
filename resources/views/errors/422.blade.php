@include('errors._page', [
    'status' => $status ?? 422,
    'title' => $title ?? 'Some details need attention',
    'message' => $message ?? 'Please fix the highlighted fields and try again.',
    'action' => 'Return home',
])
