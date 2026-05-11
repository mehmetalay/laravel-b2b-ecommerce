if(window.innerWidth <= 1068){
    $('.responsive-msg-component').hide();
}
// document.addEventListener('contextmenu', event => event.preventDefault());
$(document).keydown(function (event) {
    if (event.keyCode == 123) {
       return false;
    }
    if (event.ctrlKey && (event.keyCode === 85 || event.keyCode === 83 || event.keyCode ===65 )) {
       return false;
    }
    else if (event.ctrlKey && event.shiftKey && event.keyCode === 73)
    {
       return false;
    }
});
$(document).ready(function(){
    $('.bs-tooltip').click(function () {
       $('.bs-tooltip').tooltip("hide");
    });
    $('.close-msg-component').click(function () {
        $('.responsive-msg-component').hide();
    });
});
var App = function() {
    var MediaSize = {
        xl: 1200,
        lg: 992,
        md: 991,
        sm: 576
    };
    var ToggleClasses = {
        headerhamburger: '.toggle-sidebar',
        inputFocused: 'input-focused',
    };
    var Selector = {
        mainHeader: '.header.navbar',
        headerhamburger: '.toggle-sidebar',
        fixed: '.fixed-top',
        mainContainer: '.main-container',
        sidebar: '#sidebar',
        sidebarContent: '#sidebar-content',
        sidebarStickyContent: '.sticky-sidebar-content',
        ariaExpandedTrue: '#sidebar [aria-expanded="true"]',
        ariaExpandedFalse: '#sidebar [aria-expanded="false"]',
        contentWrapper: '#content',
        contentWrapperContent: '.container',
        mainContentArea: '.main-content',
        searchFull: '.toggle-search',
        rightBar:'.right-bar',
        overlay: {
            sidebar: '.overlay',
            cs: '.cs-overlay',
            search: '.search-overlay'
        }
    };
    var toggleFunction = {
        sidebar: function($recentSubmenu) {
            $('.sidebarCollapse').on('click', function (sidebar) {
                sidebar.preventDefault();
                $(Selector.mainContainer).toggleClass("menubar-wrapper-closed");
                $(Selector.mainHeader).toggleClass('expand-header');
                $(Selector.mainContainer).toggleClass("sbar-open");
                $('.overlay').toggleClass('show');
                $('html,body').toggleClass('sidebar-noneoverflow');
            });
        },
        overlay: function() {
            $('#dismiss, .overlay, cs-overlay').on('click', function () {
                // hide sidebar
                $(Selector.mainContainer).removeClass('menubar-wrapper-closed');
                $(Selector.mainContainer).removeClass('sbar-open');
                // hide overlay
                $('.overlay').removeClass('show');
                $('html,body').removeClass('sidebar-noneoverflow');
            });
        },
        search: function() {
            $(Selector.searchFull).click(function(event) {
               $(this).parents('.search-animated').find('.search-full').addClass(ToggleClasses.inputFocused);
               $(this).parents('.search-animated').addClass('show-search');
               $(Selector.overlay.search).addClass('show');
               $(Selector.overlay.search).addClass('show');
            });
            $(Selector.overlay.search).click(function(event) {
               $(this).removeClass('show');
               $(Selector.searchFull).parents('.search-animated').find('.search-full').removeClass(ToggleClasses.inputFocused);
               $(Selector.searchFull).parents('.search-animated').removeClass('show-search');
            });
        },
        rightbar: function() {
            $('.rightbarCollapse').on('click', function () {
                $('.rightbar-overlay').toggleClass('show');
                $('body').toggleClass('right-bar-enabled');
            });
        },
        rightbarClose: function() {
            $('.rightbar-overlay').on('click', function () {
                $('.rightbar-overlay').removeClass('show');
                $('body').removeClass('right-bar-enabled');
                // Open first tab in right bar everytime
                $('.right-bar .simplebar-content .nav-tabs .nav-item:nth-child(3) a.nav-link').removeClass('active');
                $('.right-bar .simplebar-content .nav-tabs .nav-item:nth-child(2) a.nav-link').removeClass('active');
                $('.right-bar .simplebar-content .nav-tabs .nav-item:nth-child(1) a.nav-link').addClass('active');
                $('.right-bar .simplebar-content .tab-content .tab-pane:nth-child(3)').removeClass('active');
                $('.right-bar .simplebar-content .tab-content .tab-pane:nth-child(2)').removeClass('active');
                $('.right-bar .simplebar-content .tab-content .tab-pane:nth-child(1)').addClass('active');
            });
        },
    }
    var inBuiltfunctionality = {
        mainCatActivateScroll: function() {
            const ss = new PerfectScrollbar('.menu-categories', {
                wheelSpeed:.5,
                swipeEasing:!0,
                minScrollbarLength:40,
                maxScrollbarLength:300,
                suppressScrollX : true
            });
        },
        preventScrollBody: function() {
            $('#sidebar').bind('mousewheel DOMMouseScroll', function(e) {
                var scrollTo = null;
                if (e.type == 'mousewheel') {
                    scrollTo = (e.originalEvent.wheelDelta * -1);
                }
                else if (e.type == 'DOMMouseScroll') {
                    scrollTo = 40 * e.originalEvent.detail;
                }
                if (scrollTo) {
                    e.preventDefault();
                    $(this).scrollTop(scrollTo + $(this).scrollTop());
                }
            });
        },
        functionalDropdown: function() {
            var getDropdownElement = document.querySelectorAll('.more-dropdown .dropdown-item');
            for (var i = 0; i < getDropdownElement.length; i++) {
                getDropdownElement[i].addEventListener('click', function() {
                    document.querySelectorAll('.more-dropdown .dropdown-toggle > span')[0].innerText = this.getAttribute('data-value');
                })
            }
        }
    }
    var fullScreenMode = {
        fullscreen: function() {
            var toggle;
            $('.full-screen-mode').on('click', function () {
                toggle = !toggle;
                var myId = document.getElementById('fullScreenIcon');
                if(toggle){
                    myId.classList.remove("la-compress");
                    myId.classList.add("la-compress-arrows-alt");
                    var elem = document.documentElement;
                    if (elem.requestFullscreen) {
                        elem.requestFullscreen();
                    } else if (elem.mozRequestFullScreen) { /* Firefox */
                        elem.mozRequestFullScreen();
                    } else if (elem.webkitRequestFullscreen) { /* Chrome, Safari & Opera */
                        elem.webkitRequestFullscreen();
                    } else if (elem.msRequestFullscreen) { /* IE/Edge */
                        elem.msRequestFullscreen();
                    }
                } else if(!toggle) {
                    myId.classList.remove("la-compress-arrows-alt");
                    myId.classList.add("la-compress");
                    document.getElementById("fullScreenIcon").classList.remove('helo');
                    if (document.exitFullscreen) {
                        document.exitFullscreen();
                    } else if (document.mozCancelFullScreen) {
                        document.mozCancelFullScreen();
                    } else if (document.webkitExitFullscreen) {
                        document.webkitExitFullscreen();
                    } else if (document.msExitFullscreen) {
                        document.msExitFullscreen();
                    }
                }
            })
        },
    }
    var _mobileResolution = {
        onRefresh: function() {
            var windowWidth = window.innerWidth;
            if ( windowWidth <= MediaSize.md ) {
                toggleFunction.sidebar();
            }
        },
    }
    var _desktopResolution = {
        onRefresh: function() {
            var windowWidth = window.innerWidth;
            if ( windowWidth > MediaSize.md ) {
                toggleFunction.sidebar(true);
            }
        },
    }
    function sidebarFunctionality() {
        function sidebarCloser() {
            if (window.innerWidth <= 1200 ) {
                if (!$('body').hasClass('alt-menu')) {
                    // $("#container").addClass("menubar-wrapper-closed");
                    $('.overlay').removeClass('show');
                } else {
                    $(".navbar").removeClass("expand-header");
                    $('.overlay').removeClass('show');
                    $('#container').removeClass('sbar-open');
                    $('html, body').removeClass('sidebar-noneoverflow');
                }
            } else if (window.innerWidth > 1200 ) {
                if (!$('body').hasClass('alt-menu')) {
                    $("#container").removeClass("menubar-wrapper-closed");
                    $(".navbar").removeClass("expand-header");
                    $('.overlay').removeClass('show');
                    $('#container').removeClass('sbar-open');
                    $('html, body').removeClass('sidebar-noneoverflow');
                } else {
                    $('html, body').addClass('sidebar-noneoverflow');
                    $("#container").addClass("menubar-wrapper-closed");
                    $(".navbar").addClass("expand-header");
                    $('.overlay').addClass('show');
                    $('#container').addClass('sbar-open');
                }
            }
        }
        function sidebarMobCheck() {
            if (window.innerWidth <= 991 ) {
                if ( $('.main-container').hasClass('sbar-open') ) {
                    return;
                } else {
                    sidebarCloser()
                }
            } else if (window.innerWidth > 991 ) {
                sidebarCloser();
            }
        }
        sidebarCloser();
        $(window).resize(function(event) {
            sidebarMobCheck();
        });
        // FOR RIPPLE EFFECT
        (function($, window, document, undefined) {
            'use strict';
            var $ripple = $('.js-ripple');
            $ripple.on('click.ui.ripple', function(e) {
              var $this = $(this);
              var $offset = $this.parent().offset();
              var $circle = $this.find('.ripple-ripple__circle');
              var x = e.pageX - $offset.left;
              var y = e.pageY - $offset.top;
              $circle.css({
                top: y + 'px',
                left: x + 'px'
              });
              $this.addClass('is-active');
            });
            $ripple.on('animationend webkitAnimationEnd oanimationend MSAnimationEnd', function(e) {
                $(this).removeClass('is-active');
            });
        })(jQuery, window, document);
    }
    return {
        init: function() {
            toggleFunction.overlay();
            toggleFunction.search();
            toggleFunction.rightbar();
            toggleFunction.rightbarClose();
            // Full Screen Mode
            fullScreenMode.fullscreen();
            _desktopResolution.onRefresh();
            _mobileResolution.onRefresh();
            sidebarFunctionality();
            inBuiltfunctionality.mainCatActivateScroll();
            inBuiltfunctionality.preventScrollBody();
            inBuiltfunctionality.functionalDropdown();
        }
    }
}();
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});
// debounce
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
//Aktif Pasif
$('[name="status"], [name="block_entry"], [name="amount_locked"], [name="contract_approved"], [name="is_order_closed"], [name="show_campaign_products"], [name="show_normal_products"], [name="hide_campaign_products"]').change(function () {
    var message = $(this).is(':checked') ? 'Aktif' : 'Pasif';
    $(this).closest('.form-group').find('#status-label-text').text(message);
});
// Ekleme ve Düzenleme formu
$('body').on('submit', '#data-form', function(e) {
    e.preventDefault();
    clickedButton($(this));
    var el = $('.data-form-button');
    var htmlButton = el.html();
    el.prop('disabled', true).html('<span class="spinner-border spinner-border-sm mr-1" role="status" aria-hidden="true"></span> İşleminiz yapılıyor, lütfen bekleyin...');
    $.ajax({
        url: $(this).attr('action'),
        data: new FormData(this),
        type: $(this).attr('method'),
        contentType: false,
        cache: false,
        processData: false,
        dataType: 'JSON',
        success: function(data) {
            if (data.success) {
                if (data.success.new_tab) {
                    window.open(data.success.new_tab, '_blank');
                    window.location.href = data.success.redirect;
                } else {
                    window.location.href = data.success;
                }
            } else {
                el.html(htmlButton).prop('disabled', false);
                var message = data.warning ? data.warning : data.error;
                notify((data.warning ? 'warning' : 'error'), message);
            }
        },
        error: function(xhr, status, error) {
            el.html(htmlButton).prop('disabled', false);
            notify('error', 'İstek sırasında bir hata oluştu. Lütfen site yöneticisiyle iletişime geçin.');
        }
    });
});
// Kaydet yeni, çık
function setClicked(buttonName) {
    $('button[name="' + buttonName + '"]').click(function() {
        if (!$(this).hasClass('clicked')) {
            $(this).addClass('clicked');
            $('button[name!="'+ buttonName +'"]').removeClass('clicked');
        }
    });
}
setClicked("save_and_go");
setClicked("save_and_copy");
setClicked("save_and_new");
setClicked("save_and_close");
setClicked("save_and_pay");
function clickedButton(that) {
    var clickedButton = $('button.clicked');
    if (clickedButton.length > 0) {
        var buttonName = clickedButton.attr('name');

        // Aksiyon kısmını resetle, sadece seçilen buton eklenir
        var action = that.attr('action').split('?')[0]; // Aksiyonun sadece temel kısmını al

        action += '?' + buttonName + '=1';
        that.attr('action', action);
    }
}
//Draggable Bootstrap modal
$('.modal-dialog').draggable({
    'handle':'.modal-header'
});
// Fiyat formatı
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
// Filtreleri temizle
function removeFiltersFromURL() {
    var url = window.location.href;
    if (url.indexOf('?') !== -1) {
        url = url.split('?')[0];
    }
    window.location.href = url;
}
// Checkbox True False
$(document).ready(function() {
    updateHiddenInput();
    $('input[type=checkbox]').change(function() {
        updateHiddenInput.call(this);
    });
    function updateHiddenInput() {
        var isChecked = $(this).is(':checked');
        $(this).prev('input[type=hidden]').val(isChecked ? 'true' : 'false');
    }
});

function readURL(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
            var previewSelector = $(input).attr("data-preview");
            var previewElement = $(previewSelector); // Doğrudan ID ile seçiyoruz.

            if (previewElement.length) {
                previewElement.css("background-image", "url(" + e.target.result + ")").hide().fadeIn(650);
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

$(document).on("change", "input[type='file']", function () {
    readURL(this);
});


