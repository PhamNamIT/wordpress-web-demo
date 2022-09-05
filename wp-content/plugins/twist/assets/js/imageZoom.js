(function ($) {
    var defaults = {
        callback: false,
        target: false,
        duration: 120,
        touch: true,
        onZoomIn: false,
        onZoomOut: false,
    };

    $.zoom = function (target, source, img, magnify) {
        var targetHeight,
            targetWidth,
            sourceHeight,
            sourceWidth,
            xRatio,
            yRatio,
            offset,
            $target = $(target),
            position = $target.css('position'),
            $source = $(source);

        $target.css('position', /(absolute|fixed)/.test(position) ? position : 'relative');
        $target.css('overflow', 'hidden');

        img.style.width = img.style.height = '';

        $(img)
            .addClass('zoomImg')
            .css({
                position: 'absolute',
                top: 0,
                left: 0,
                opacity: 0,
                width: img.width * magnify,
                height: img.height * magnify,
                border: 'none',
                maxWidth: 'none',
                maxHeight: 'none'
            })
            .appendTo(target);

        return {
            init: function () {
                targetWidth = $target.outerWidth();
                targetHeight = $target.outerHeight();

                if (source === $target[0]) {
                    sourceWidth = targetWidth;
                    sourceHeight = targetHeight;
                } else {
                    sourceWidth = $source.outerWidth();
                    sourceHeight = $source.outerHeight();
                }

                xRatio = (img.width - targetWidth) / sourceWidth;
                yRatio = (img.height - targetHeight) / sourceHeight;

                offset = $source.offset();
            },
            move: function (e) {
                var left = (e.pageX - offset.left),
                    top = (e.pageY - offset.top);

                top = Math.max(Math.min(top, sourceHeight), 0);
                left = Math.max(Math.min(left, sourceWidth), 0);

                img.style.left = (left * -xRatio) + 'px';
                img.style.top = (top * -yRatio) + 'px';
            }
        };
    };

    $.fn.zoom = function (options) {
        return this.each(function () {
            var settings = $.extend({
                borderStyle: null,
                borderWidth: null,
                borderColor: null,
                imgWidth: false,
                magnify: false,
                on: false,
                url: false,
                complete: null,
            }, defaults, options || {}),
                target = settings.target || this,
                source = this,
                $source = $(source),
                $target = $(target),
                img = document.createElement('img'),
                $img = $(img),
                mousemove = 'mousemove',
                clicked = false,
                touched = false;

            if (settings.borderStyle) $(this).css('border-style', settings.borderStyle);
            if (settings.borderWidth) $(this).css('border-width', settings.borderWidth);
            if (settings.borderColor) $(this).css('border-color', settings.borderColor);
            if (settings.imgWidth) $(this).css('width', settings.imgWidth);
            if (settings.magnify) settings.magnify;
            if (settings.on) settings.on;
            // if (settings.url) settings.url;
            if (!settings.url) {

                $urlElement = $source.find('img');

                settings.url = $urlElement.data('large_image');
            }

            if ($.isFunction(settings.complete)) {
                settings.complete.call(this);
            }

            img.onload = function () {
                var zoom = $.zoom(target, source, img, settings.magnify);

                function start(e) {
                    zoom.init();
                    zoom.move(e);

                    $img.stop()
                        .fadeTo($.support.opacity ? settings.duration : 0, 1, $.isFunction(settings.onZoomIn) ? settings.onZoomIn.call(img) : false);
                }

                function stop() {
                    $img.stop()
                        .fadeTo(settings.duration, 0, $.isFunction(settings.onZoomOut) ? settings.onZoomOut.call(img) : false);
                }

                if (settings.on === 'grab') {
                    $source
                        .on('mousedown',
                            function (e) {
                                if (e.which === 1) {
                                    $(document).one('mouseup',
                                        function () {
                                            stop();
                                            $(document).off(mousemove, zoom.move);
                                        }
                                    );

                                    start(e);
                                    $(document).on(mousemove, zoom.move);
                                    e.preventDefault();
                                }
                            }
                        );
                } else if (settings.on === 'click') {
                    $source.on('click',
                        function (e) {
                            if (clicked) {
                                return;
                            } else {
                                clicked = true;
                                start(e);
                                $(document).on(mousemove, zoom.move);
                                $(document).one('click',
                                    function () {
                                        stop();
                                        clicked = false;
                                        $(document).off(mousemove, zoom.move);
                                    }
                                );
                                return false;
                            }
                        }
                    );
                } else if (settings.on === 'toggle') {
                    $source.on('click',
                        function (e) {
                            if (clicked) {
                                stop();
                            } else {
                                start(e);
                            }
                            clicked = !clicked;
                        }
                    );
                } else if (settings.on === 'mouseover') {
                    zoom.init();

                    $source
                        .on('mouseenter', start)
                        .on('mouseleave', stop)
                        .on(mousemove, zoom.move);
                }

                if (settings.touch) {
                    $source
                        .on('touchstart', function (e) {
                            e.preventDefault();
                            if (touched) {
                                touched = false;
                                stop();
                            } else {
                                touched = true;
                                start(e.originalEvent.touches[0] || e.originalEvent.changedTouches[0]);
                            }
                        })
                        .on('touchmove', function (e) {
                            e.preventDefault();
                            zoom.move(e.originalEvent.touches[0] || e.originalEvent.changedTouches[0]);
                        });
                }

                if ($.isFunction(settings.callback)) {
                    settings.callback.call(img);
                }
            };

            img.src = settings.url;
        });
    };

    $.fn.zoom.defaults = defaults;
}(window.jQuery));