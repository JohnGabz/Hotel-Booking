@include('errors._page', [
    'status' => $status ?? 500,
    'title' => $title ?? 'Something went wrong',
    'message' => $message ?? 'Something unexpected happened on our side. Please try again in a moment.',
    'action' => 'Return home',
])
