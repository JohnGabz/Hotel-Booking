@include('errors._page', [
    'status' => $status ?? 404,
    'title' => $title ?? 'We could not find that page',
    'message' => $message ?? 'The page or record you requested may have been moved, deleted, or mistyped.',
    'action' => 'Return home',
])
