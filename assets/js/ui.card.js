/* global Translator, app, moment */

(function ui_card(ui, $)
{

    
    var review_minimum_length = 100;
    
    ui.setup_edit = function setup_edit(review_id, mine)
    {
        var review = $('#review-' + review_id);
        var key = mine
            ? 'card.reviews.edit_mine'
            : 'card.reviews.edit_moderator';
    
        var link = $('<a href="#" class="review-edit-link"></a>')
            .text(Translator.trans(key));
    
        review.find('.review-author').append(
            $('<div class="text-right"></div>').append(link)
        );
    };
    
    ui.setup_write = function setup_write()
    {
        var button = $('<button class="pull-right btn btn-default" id="review-button"><span class="glyphicon glyphicon-plus"></span> '
            + Translator.trans('card.reviews.write') + '</button>');
    
        $('#reviews-header').prepend(button);
    };
    
    ui.check_review = function check_review(event)
    {
        event.preventDefault();
    
        var form = $('#review-edit-form');
        var review_id = form.find('input[name="review_id"]').val();
    
        // Keep the minimum for new reviews.
        // Historical reviews may be shorter.
        if (!review_id &&
            $('#review-form-preview').text().length < review_minimum_length) {
            alert(Translator.trans('card.reviews.alerts.minimum', {
                min: review_minimum_length
            }));
            return;
        }
    
        var url = Routing.generate(
            review_id ? 'card_review_edit' : 'card_review_post'
        );
    
        var button = form.find('button[type="submit"]');
        button.prop('disabled', true);
    
        $.ajax(url, {
            data: form.serialize(),
            type: 'POST',
            dataType: 'json',
    
            success: function (data)
            {
                if (review_id) {
                    $('#review-' + review_id + ' .review-text')
                        .html(data.review_html);
    
                    ui.notify(
                        form,
                        'success',
                        Translator.trans('card.reviews.updated')
                    );
                } else {
                    ui.notify(
                        form,
                        'success',
                        Translator.trans('card.reviews.posted')
                    );
                }
    
                form.hide();
            },
    
            error: function (jqXHR)
            {
                var message = (
                    jqXHR.responseJSON &&
                    jqXHR.responseJSON.message
                ) || 'Unable to save review.';
    
                ui.notify(form, 'danger', message);
            },
    
            complete: function ()
            {
                button.prop('disabled', false);
            }
        });
    };
    
    ui.notify = function notify(form, type, message)
    {
        form.siblings('.review-notice').remove();
    
        var alert = $('<div class="alert review-notice" role="alert"></div>')
            .addClass('alert-' + type)
            .text(message);
    
        $(form).after(alert);
    };
    
    ui.open_review_form = function open_review_form(
        review_id,
        text,
        csrf_token
    ) {
        var form = $('#review-edit-form');
    
        form.siblings('.review-notice').remove();
        form.find('input[name="review_id"]').val(review_id || '');
        form.find('input[name="_token"]').val(csrf_token || '');
    
        if (!$('#review-form-text').length) {
            form.append(
                '<div><div class="form-group">'
                + '<textarea id="review-form-text" class="form-control" rows="20" name="review" placeholder="'
                + Translator.trans('card.reviews.hint', {
                    min: review_minimum_length
                })
                + '"></textarea>'
                + '</div><div class="well text-muted" id="review-form-preview">'
                + '<small>Preview. Look <a href="http://daringfireball.net/projects/markdown/dingus">'
                + 'here</a> for a Markdown syntax reference.</small>'
                + '</div>'
                + '<button type="submit" class="btn btn-success">'
                + Translator.trans('card.reviews.submit')
                + '</button></div>'
            );
    
            form.on('submit', ui.check_review);
    
            app.markdown.setup(
                '#review-form-text',
                '#review-form-preview'
            );
    
            app.textcomplete.setup('#review-form-text');
        }
    
        form.show();
    
        $('#review-form-text')
            .val(text || '')
            .trigger('keyup')
            .focus();
    };
    
    ui.write_review_open = function write_review_open(event)
    {
        event.preventDefault();
    
        $(this).remove();
        ui.open_review_form('', '');
    };
    
    ui.edit_review_open = function edit_review_open(event)
    {
        event.preventDefault();
    
        var review_id = $(this)
            .closest('article.review')
            .data('id');
    
        $.getJSON(
            Routing.generate('card_review_edit_data', {
                id: review_id
            })
        )
        .done(function (data) {
            ui.open_review_form(
                data.review_id,
                data.text_md,
                data.csrf_token
            );
        })
        .fail(function () {
            alert('Unable to load this review for editing.');
        });
    };


    /**
     * The user has clicked on "Add a comment"
     * Thsi function replace that button with a one-line for to input and submit the comment
     */
    ui.write_comment = function write_comment(event)
    {
        event.preventDefault();
        $(this).replaceWith('<div class="input-group"><input type="text" class="form-control" name="comment" placeholder="' + Translator.trans('card.reviews.comments.hint') + '"><span class="input-group-btn"><button class="btn btn-primary" type="submit">' + Translator.trans('card.reviews.comments.post') + '</button></span></div>');
    };

    /**
     * The user has clicked on "Submit the comment"
     * @param event
     */
    ui.form_comment_submit = function form_comment_submit(event)
    {
        event.preventDefault();
        var form = $(this);
        if(form.data('submitted'))
            return;
        form.data('submitted', true);
        $.ajax(form.attr('action'), {
            data: form.serialize(),
            type: 'POST',
            dataType: 'json',
            success: function (data, textStatus, jqXHR)
            {
                ui.notify(form, 'success', Translator.trans('card.reviews.comments.posted'));
                form.remove();
            },
            error: function (jqXHR, textStatus, errorThrown)
            {
                console.log('[' + moment().format('YYYY-MM-DD HH:mm:ss') + '] Error on ' + this.url, textStatus, errorThrown);
                ui.notify(form, 'danger', jqXHR.responseBody.message);
            }
        });
    };

    ui.like_review = function like_review(event)
    {
        event.preventDefault();
        var obj = $(this);
        var review_id = obj.closest('article.review').data('id');
        $.post(Routing.generate('card_review_like'), {
            id: review_id
        }, function (data, textStatus, jqXHR)
        {
            obj.find('.num').text(jqXHR.responseJSON.nbVotes);
        });
    };

    /**
     * called when the DOM is loaded
     * @memberOf ui
     */
    ui.on_dom_loaded = function on_dom_loaded()
    {
        app.user.loaded.done(function ()
        {
            var currentId = String(app.user.data.id);
            var moderator = !!app.user.data.can_moderate_reviews;
            var hasOwnReview = false;
    
            $('article.review').each(function ()
            {
                var review = $(this);
                var mine = String(review.attr('data-author-id')) === currentId;
    
                if (mine) {
                    hasOwnReview = true;
                }
    
                if (mine || moderator) {
                    ui.setup_edit(review.data('id'), mine);
                }
            });
    
            if (!hasOwnReview) {
                ui.setup_write();
            }
        });
    
        $(window.document).on(
            'click',
            '.btn-write-comment',
            ui.write_comment
        );
    
        $(window.document).on(
            'click',
            '.social-icon-like',
            ui.like_review
        );
    
        $(window.document).on(
            'click',
            '#review-button',
            ui.write_review_open
        );
    
        $(window.document).on(
            'click',
            '.review-edit-link',
            ui.edit_review_open
        );
    
        $(window.document).on(
            'submit',
            'form.form-comment',
            ui.form_comment_submit
        );
    };


})(app.ui, jQuery);
