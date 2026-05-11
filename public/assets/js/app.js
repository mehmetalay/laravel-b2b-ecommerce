function debounce(func, wait, immediate) {
    var timeout;
    return function() {
        var context = this, args = arguments;
        var later = function() {
            timeout = null;
            if (!immediate) func.apply(context, args);
        };
        var callNow = immediate && !timeout;
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
        if (callNow) func.apply(context, args);
    };
};
// Loader
function showLoader() {
    var loader = $(`
        <div class="fullpage-loader bg-transparent">
            <img src="/assets/images/logo.png" alt="Özdoğan" />
        </div>
    `).appendTo('body');
    loader.show();
    return loader;
}
// price format
$("input[data-format='price']").on({
    input: function() {
        var caretPos = getCaretPosition($(this));
        var originalVal = $(this).val();
        formatCurrency($(this));
        var newVal = $(this).val();
        var diff = newVal.length - originalVal.length;
        setCaretPosition($(this), caretPos + diff);
    },
    blur: function() {
        formatCurrency($(this), "blur");
    }
});
function formatCurrency(input, blur) {
    var input_val = input.val();
    if (input_val === '') {
        return;
    }
    var original_len = input_val.length;
    if (input_val.indexOf(".") >= 0) {
        var decimal_pos = input_val.indexOf(".");
        var left_side = input_val.substring(0, decimal_pos);
        var right_side = input_val.substring(decimal_pos);
        left_side = formatNumber(left_side);
        right_side = formatNumber(right_side);
        right_side = right_side.substring(0, 2);
        input_val = left_side + "." + right_side;
    } else {
        input_val = formatNumber(input_val);
        if (input_val !== '' && blur === "blur") {
            input_val += ".00";
        }
    }
    input.val(input_val);
}
function formatNumber(n) {
    return n.replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}
function getCaretPosition(input) {
    if (input[0].selectionStart) {
        return input[0].selectionStart;
    } else if (document.selection) {
        input.focus();
        var range = document.selection.createRange();
        range.moveStart('character', -input.val().length);
        return range.text.length;
    }
    return 0;
}
function setCaretPosition(input, pos) {
    if (input[0].setSelectionRange) {
        input[0].setSelectionRange(pos, pos);
    } else if (input.createTextRange) {
        var range = input.createTextRange();
        range.collapse(true);
        range.moveEnd('character', pos);
        range.moveStart('character', pos);
        range.select();
    }
}
$("input[data-format='price']").on("keydown", function (e) {
    if (e.key === "," || e.keyCode === 188 || e.keyCode === 110) {
        e.preventDefault();

        var input = $(this);
        var caretPos = getCaretPosition(input);
        var val = input.val();

        if (val.indexOf(".") === -1) {
            input.val(
                val.substring(0, caretPos) + "." + val.substring(caretPos)
            );
            setCaretPosition(input, caretPos + 1);
        }
    }
});
// end price format
$(document).on('touchstart', function(e) {
    if (!$(e.target).is('input, textarea')) {
        $('input, textarea').blur();
    }
});

