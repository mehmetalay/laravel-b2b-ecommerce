@php
    if ($type == 'success') {
        $data = [
            'type' => $type,
            'message' => $message,
            'url' => $url
        ];
    } else {
        $data = [
            'type' => $type,
            'message' => $message
        ];
    }
@endphp

<script type="text/javascript">
    var data = @json($data);
    window.parent.paymentPostMessage(data, '*');
</script>
