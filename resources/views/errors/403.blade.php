@include('errors._page', [
    'status' => $status ?? 403,
    'title' => $title ?? 'This area is restricted',
    'message' => $message ?? 'You do not have permission to open this page or perform this action.',
    'action' => 'Return home',
])
