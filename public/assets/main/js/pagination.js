let rootPagi = $('.pagination');

let cpbEnd = function (signal, id) {
    let e = false;
    do {
        if (signal == 1) {
            if ($(this).prev().prev().data('id') != "?") {
                $(this).prev().prev().remove();
            } else {
                e = true;
                break;
            }
        } else if (signal == 0) {
            if ($(this).next().next().data('id') != "?") {
                $(this).next().next().remove();
            } else {
                e = true;
                break;
            }
        } else if (signal == 2) {
            if ($(this).siblings('[data-id="' + id + '"]').next().data('id') != "?") {
                $(this).siblings('[data-id="' + id + '"]').next().remove();
            } else {
                e = true;
                break;
            }
        } else if (signal == 3) {
            if ($(this).siblings('[data-id="' + id + '"]').prev().data('id') != "?") {
                $(this).siblings('[data-id="' + id + '"]').prev().remove();
            } else {
                e = true;
                break;
            }
        } else {
            return false;
        }
    } while (e == false)
};

let controlPaginationButton = function (state, rootPagi, p, lAP) {
    let sb = 7,
        asbn = 2,
        abn = 1,
        nbn,
        pbn,
        pn = true;

    const mark = '<div class="page-link page-item disabled" data-id="?">...</div>';

    if (sb < 7) {
        sb = 7;
    } else if (sb > 8) {
        sb = 8;
    }

    if (asbn < 1) {
        asbn = 1;
    } else if (asbn > 5) {
        asbn = 5;
    }

    /* 
        state 0 = load mode
        state 1 = click mode
        state 2 = reform mode when total pages is change
    */

    if (state == 0 || state == 2) {
        if (lAP != undefined) {
            abn = lAP;
        } else {
            abn = rootPagi.find('.page-link.active').data('id');
        }
        abn = parseInt(abn);
        rootPagi.children('.page-link').remove();
    }

    if (state == 0) {
        for (let i = 1; i <= p; i++) {
            if (sb >= p) {
                rootPagi.append('<div class="page-link page-item" data-id="' + i + '">' + i + '</div>');
            } else {
                if (i <= 5 && i < (p - 2) || i == p) {
                    rootPagi.append('<div class="page-link page-item" data-id="' + i + '">' + i + '</div>');
                } else {
                    if (!rootPagi.find('[data-id="?"]').length) {
                        rootPagi.append(mark);
                    }
                }
            }
            if (pn == true) {
                if (i == 1) {
                    rootPagi.prepend('<div class="page-link page-item prev"><i class="fas fa-angle-left"></i><span class="sr-only">Prev</span></div>');
                }
                if (i == p) {
                    rootPagi.append('<div class="page-link page-item next"><i class="fas fa-angle-right"></i><span class="sr-only">Next</span></div>');
                }
            }
        }
        rootPagi.find('.page-link[data-id="1"]').addClass('active');
    } else if (state == 1) {
        abn = $(this).data('id');
        nbn = $(this).next().data('id');
        pbn = $(this).prev().data('id');
        // Signal 0 = state code for prev button number is hidden button or ... from current active button
        // Signal 1 = state code for next button number is hidden button or ... from current active button
        // Signal 2 = state code for reform button when click on first page
        // Signal 3 = state code for reform button when click on last page
        let signal;
        if (abn == 1) {
            $(this).siblings('.prev').addClass('disabled');
        } else {
            $(this).siblings('.prev').removeClass('disabled');
        }

        if (abn == p) {
            $(this).siblings('.next').addClass('disabled');
        } else {
            $(this).siblings('.next').removeClass('disabled');
        }

        $(this).parent('.pagination').attr('data-id', abn);

        if (nbn == "?" && abn != 1) {
            if (p - abn <= asbn) {
                asbn = p - abn - 1;
            }
            if (p - abn - 1 - asbn == 1 && asbn > 1) {
                asbn--;
            }
            if (asbn == 1 && p - abn == 3) {
                asbn++;
            }
            signal = 1;
        } else if (pbn == "?" && abn != p) {
            if (abn - 2 < asbn) {
                asbn = abn - 2;
            }
            if (abn - 2 - asbn == 1 && asbn > 1) {
                asbn--;
            }
            if (asbn == 1 && abn - 1 == 3) {
                asbn++;
            }
            signal = 0;
        } else if (abn == 1 && nbn == "?") {
            asbn = 4;
            signal = 2;
        } else if (abn == p && pbn == "?") {
            asbn = 4;
            signal = 3;
        } else {
            return false;
        }

        for (let i = asbn; i > 0; i--) {
            if (signal == 1) {
                if (i == 1) {
                    if (!$(this).siblings('[data-id="1"]').next('[data-id="?"]').length) {
                        $(this).siblings('[data-id="1"]').after(mark);
                    }
                }
                $(this).after('<div class="page-link page-item" data-id="' + (abn + i) + '">' + (abn + i) + '</div>');
            } else if (signal == 0) {
                if (i == 1) {
                    if (!$(this).siblings('[data-id="' + p + '"]').prev('[data-id="?"]').length) {
                        $(this).siblings('[data-id="' + p + '"]').before(mark);
                    }
                }
                $(this).before('<div class="page-link page-item" data-id="' + (abn - i) + '">' + (abn - i) + '</div>');
            } else if (signal == 2) {
                index = $(this).index() + asbn;
                $(this).siblings().eq(index).remove();
                $(this).after('<div class="page-link page-item" data-id="' + (abn + i) + '">' + (abn + i) + '</div>');
            } else if (signal == 3) {
                index = ($(this).siblings().length + 1) - $(this).index() + asbn;
                $(this).siblings().eq(-index).remove();
                $(this).before('<div class="page-link page-item" data-id="' + (abn - i) + '">' + (abn - i) + '</div>');
            } else {
                return false;
            }
        }

        if (signal == 1) {
            j = false;
            cpbEnd.call(this, signal);
            if (p - abn - 1 <= asbn) {
                $(this).siblings('[data-id="?"]').last().remove();
            }
        } else if (signal == 0) {
            cpbEnd.call(this, signal);
            if (abn - 2 <= asbn) {
                $(this).siblings('[data-id="?"]').first().remove();
            }
        } else if (signal == 2) {
            setTimeout(() => {
                $(this).siblings('[data-id="?"]').remove();
                setTimeout(() => {
                    $('[data-id="' + p + '"]').before(mark);
                    if (p > sb) {
                        cpbEnd.call(this, signal, (asbn + 1));
                    }
                }, 0);
            }, 0);
        } else if (signal == 3) {
            setTimeout(() => {
                $(this).siblings('[data-id="?"]').remove();
                setTimeout(() => {
                    $('[data-id="' + 1 + '"]').after(mark);
                    if (p > sb) {
                        cpbEnd.call(this, signal, (p - asbn));
                    }
                }, 0);
            }, 0);
        } else {
            return false;
        }
    } else if (state == 2) {
        for (let i = 1; i <= p; i++) {
            if (sb >= p) {
                rootPagi.append('<div class="page-link page-item" data-id="' + i + '">' + i + '</div>');
            } else {
                if (i == 1 || i == p) {
                    rootPagi.append('<div class="page-link page-item" data-id="' + i + '">' + i + '</div>');
                } else {
                    if (abn < 5 && i <= 5) {
                        rootPagi.append('<div class="page-link page-item" data-id="' + i + '">' + i + '</div>');
                        if (i == 5) {
                            rootPagi.append(mark);
                        }
                    } else if (abn > p - 4 && i >= p - 4) {
                        if (i == p - 4) {
                            rootPagi.append(mark);
                        }
                        rootPagi.append('<div class="page-link page-item" data-id="' + i + '">' + i + '</div>');
                    } else if (abn >= 5 && abn <= p - 4) {
                        if (i == abn - asbn && i >= 4 || abn == 5 && i == 4) {
                            rootPagi.append(mark);
                        }
                        if (i >= abn - asbn && i >= 4 && i <= abn + asbn && i < p - 2) {
                            rootPagi.append('<div class="page-link page-item" data-id="' + i + '">' + i + '</div>');
                        }
                        if (i == abn + asbn && i == p - 2 || i != abn + asbn && i == p - 2) {
                            rootPagi.append(mark);
                        }
                    } else {
                        continue;
                    }
                }
            }

            if (pn == true) {
                if (i == 1) {
                    rootPagi.prepend('<div class="page-link page-item prev"><i class="fas fa-angle-left"></i><span class="sr-only">Prev</span></div>');
                }
                if (i == p) {
                    rootPagi.append('<div class="page-link page-item next"><i class="fas fa-angle-right"></i><span class="sr-only">Next</span></div>');
                }
            }
            
            if (abn >= p) {
                rootPagi.find('.page-link[data-id="' + p + '"]').addClass('active');
            } else {
                rootPagi.find('.page-link[data-id="' + abn + '"]').addClass('active');
            }
        }
    } else {
        return false;
    }

    rootPagi.attr('data-id', rootPagi.find('.page-link.active').data('id'));

    let cabn = rootPagi.find('.page-link.active').data('id');
    if (cabn >= 1000) {
        rootPagi.addClass('big-data');
    } else {
        rootPagi.removeClass('big-data');
    }

    if (cabn == 1) {
        rootPagi.children('.prev').addClass('disabled');
    }

    if (cabn == p) {
        rootPagi.children('.next').addClass('disabled');
    }
};

controlPaginationButton(0, rootPagi, rootPagi.data('pages'));

$('.pagination').on('click', '.page-link:not(.next):not(.prev):not(.disabled)', function () {
    $(this).addClass('active').siblings().removeClass('active');
    controlPaginationButton.call( $(this), 1, rootPagi, $(this).parent('.pagination').data('pages'));
});

$('.pagination').on('click', '.page-link.next:not(.disabled)', function () {
    $(this).siblings('.page-link:not(.next):not(.prev):not(.disabled).active').next().click();
});

$('.pagination').on('click', '.page-link.prev:not(.disabled)', function () {
    $(this).siblings('.page-link:not(.next):not(.prev):not(.disabled).active').prev().click();
});