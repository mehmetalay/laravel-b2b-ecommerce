<script>
    const pushAdminFlashNotify = (type, message) => {
        if (!message) {
            return;
        }

        notify(type, message);
    };

    pushAdminFlashNotify('success', @json(session('success')));
    pushAdminFlashNotify('error', @json(session('error')));
    pushAdminFlashNotify('warning', @json(session('warning')));
</script>
