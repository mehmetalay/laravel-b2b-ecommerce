function notification(message, type) {
    if (type == 'success') {
        color_code = '#8dbf42';
    } else if (type == 'error') {
        color_code = '#ad212f';
    } else if (type == 'warning') {
        color_code = '#e2a03f';
    }
    Snackbar.show({
        text: message, actionTextColor: '#fff', backgroundColor: color_code, pos: 'top-right', showAction: false
    });
}
