$(function($) {
    $('div.genemu_jqueryimage').live('create', function() {
        var $widget = $(this);
        var $settings = $widget.data('settings') || {};
        var $filters = $widget.data('filters') || [];
        var $thumbnails = $widget.data('thumbnails') || [];
        var $thumbnail = $widget.data('thumbnail') || {};
        var $selected = $widget.data('selected') || null;
        var $value = $widget.data('value') || null;
        var $width = $widget.data('width') || 0;
        var $height = $widget.data('height') || null;
        var $id = $settings.id || null;

        if (!$id) {
            return;
        }

         // Base path for apps not on DocumentRoot
         var $basePath = $settings.assetfolder;
         $basePath = $basePath.substr(0, $basePath.length - $settings.folder.length);

        var $field = $('#' + $id);
        var $form = $field.closest('form');
        var $preview = $('#image_' + $id + '_img_preview');
        var $preview_img_default = $preview.attr('src') || ($basePath + 'bundles/genemuform/images/default.png');
        var $remove = $('#image_' + $id + '_remove');
        var $options = $('#image_' + $id + '_options');


        var $coords = {};
        var $crop = null;
        var $ratio = 1;

        var $configs = $.extend({}, $settings, {
            queueID: 'image_' + $id + '_queue',
            width: 19,
            height: 19,
            onOpen: function(event, ID, fileObj) {
                $options.hide();
                if ($crop) {
                    $crop.destroy();
                }
                $preview.hide();
            },
            onComplete: function(event, queueID, fileObj, response, data) {
                var $response = eval('(' + response + ')');

                // add if and only if path is relative
                if ($response.thumbnail.file.search(/^[/\\]/) < 0) {
                    $response.thumbnail.file = $basePath + $response.thumbnail.file;
                }
                $field.val($response.file);
                if ($response.result == '1') {
                    createCrop({
                        image: $response.image,
                        thumbnail: $response.thumbnail
                    });
                } else {
                    removePhoto();
                    alert('Server error, try again later');
                }
            },
            onAllComplete: function(event, data) {
                $preview.show();
                $options.fadeIn();
            },
            onError: function() {
                removePhoto();
                alert('Upload error, try again later');
            }
        });
        delete $configs.id;

        var createCrop = function (data) {
            if ($crop) {
                $crop.destroy();
            }

            // add if and only if path is relative
            if (data.thumbnail.file.search(/^[/\\]/) < 0) {
                data.thumbnail.file = $basePath + data.thumbnail.file;
            }
            var $img = new Image();

            $($img).load(function() {
                var widthMax = $thumbnail && $thumbnail.width || 500;

                $ratio = data.image.width > widthMax ? data.image.width / widthMax : 1;
                $('.crop', $options).hide();

                $preview
                    .width(Math.round(data.image.width / $ratio))
                    .height(Math.round(data.image.height / $ratio))
                    .attr('src', this.src);
                $remove.fadeIn();

                if (!$crop) {
                    $options.fadeIn();
                }

                $preview.Jcrop({
                                   keySupport: false, // Scroll/jump issue http://deepliquid.com/content/Jcrop_Troubleshooting.html
                                   onSelect: checkCoords,
                                   onChange: checkCoords
                               }, function() {
                    $crop = this;
                });
            }).attr('src', data.thumbnail.file);
        };

        var checkCoords = function(coords) {
            if (coords.w > 0 && coords.h > 0) {
                $('.crop', $options).fadeIn();

                $coords = {
                    x: coords.x * $ratio,
                    y: coords.y * $ratio,
                    w: coords.w * $ratio,
                    h: coords.h * $ratio
                };
            } else {
                $('.crop', $options).fadeOut();
            }
        };

        $('.change', $options).click(function(ev) {
            var $this = $(this);
            var $regex = new RegExp('^\\b(.*?) (.*)\\b', 'g');
            var $filter = $this.attr('class').replace($regex, '$1');

            var $data = {
                filter: $filter,
                image: $field.val(),
                opacity: 0.5
            };

            ev.preventDefault();
            if ($options.find('.loading').length) {
                alert('Already working, please wait...');
                return;
            }

            if ('crop' === $filter && !$.isEmptyObject($coords)) {
                $data = $.extend($data, $coords);
            }

            if (
                $.inArray($filter, $filters) !== -1 ||
                ( 'crop' === $filter && !$.isEmptyObject($coords) )
                ) {
                $this.addClass('loading');
                if ($crop) {
                    $crop.disable();
                }

                $.ajax({
                           type: 'POST',
                           url: $settings.genemu_form_image,
                           data: $data,
                           dataType: 'json',
                           success: function(data) {
                               if ($crop) {
                                   $crop.enable();
                               }

                               if ('1' === data.result) {
                                   $field.val(data.file);
                                   createCrop({
                                                  image: data.image,
                                                  thumbnail: $.isEmptyObject(data.thumbnail) ? $.extend(data.image, {
                                                      file: data.file
                                                  }) : data.thumbnail
                                              });
                               } else {
                                   alert('Error');
                               }

                               $this.removeClass('loading');
                           }
                       });
            }
        });

        var removePhoto = function() {
            if ($crop) {
                $crop.destroy();
            }
            $preview
                .attr('src', $preview_img_default)
                .width(96)
                .height(96);
            $field.val('');
            $options.fadeOut();
            $remove.fadeOut();
            $preview.fadeIn();
        };

        $remove.click(function() {
            removePhoto();
        }).hide();
        $options.hide();

        if ($value && !$.isEmptyObject($value)) {
            createCrop({
                thumbnail: {
                    file: $thumbnail && !$.isEmptyObject($thumbnail) ? $thumbnail.file : $value,
                    width: $thumbnail && !$.isEmptyObject($thumbnail) ? $thumbnail.width : ($width || 0),
                    height: $thumbnail && !$.isEmptyObject($thumbnail) ? $thumbnail.height : ($height || 0)
                },
                image: {
                    width: $width || 0,
                    height: $height
                }
            });
        }

        $field.uploadify($configs);
    });
});
