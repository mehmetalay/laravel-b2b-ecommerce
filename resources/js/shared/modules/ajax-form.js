document.addEventListener('submit', (e) => {
    const ajaxForm = e.target.closest('[data-ajax-form]');
    if (!ajaxForm) {
        return;
    }

    e.preventDefault();
    clearFormErrors(ajaxForm);

    const submitter = e.submitter instanceof HTMLElement ? e.submitter : null;

    let button = submitter;
    if (!button) {
        button = ajaxForm.querySelector('[data-ajax-submit]');
    }
    if (!button && ajaxForm.id) {
        button = document.querySelector(`[data-ajax-submit][form="${ajaxForm.id}"]`);
    }

    const formData = new FormData(ajaxForm);
    if (submitter && submitter.name) {
        formData.append(submitter.name, submitter.value || '1');
    }

    setLoading(button, true);

    axiosRequest.post(ajaxForm.action, formData, {
        onSuccess: (result) => {
            if (result?.status !== 'success') {
                return;
            }

            notify('success', result.message || 'Islem basariyla tamamlandi.');

            if (result.redirect) {
                setTimeout(() => {
                    window.location.href = result.redirect;
                }, 1000);
            }
        },
        onValidationError: (errors) => {
            handleFormValidationErrors(errors, ajaxForm);

            const firstErrorGroup = Object.values(errors || {})[0];
            const firstError = Array.isArray(firstErrorGroup) ? firstErrorGroup[0] : firstErrorGroup;
            if (firstError) {
                notify('error', firstError);
            }
        },
        onError: (payload) => {
            if (payload?.status === 'warning') {
                notify('warning', payload.message || 'Islem uyari ile tamamlandi.');

                if (payload.redirect) {
                    setTimeout(() => {
                        window.location.href = payload.redirect;
                    }, 1000);
                }

                return;
            }

            if (payload?.status === 'error') {
                notify('error', payload.message || 'Islem sirasinda bir hata olustu.');
                return;
            }

            notify('error', payload?.message || 'Istek sirasinda bir hata olustu. Lutfen tekrar deneyin.');
        },
        onComplete: () => {
            setLoading(button, false);
        },
    });
});
