jQuery(function () {
    jQuery('.tweets_list_btn').click(function () {
        jQuery('#twitter-list-container').toggleClass('open');
        jQuery('#instagram-list-container').removeClass('open');
        jQuery('#pinterest-list-container').removeClass('open');
    });
    jQuery('.instagram_list_btn').click(function () {
        jQuery('#instagram-list-container').toggleClass('open');
        jQuery('#twitter-list-container').removeClass('open');
        jQuery('#pinterest-list-container').removeClass('open');
    });
    jQuery('.pinterest_list_btn').click(function () {
        jQuery('#pinterest-list-container').toggleClass('open');
        jQuery('#twitter-list-container').removeClass('open');
        jQuery('#instagram-list-container').removeClass('open');
    });
});

(function($){

    $(window).on('load', function(){
        if($('#instagram-list-container').length !== 0)
            $.instagramFeed({
                'username': $('#instagram-list-container').data('account'),
                'container': "#instagram-list-container",
                'display_profile': false,
                'display_biography': false,
                'display_gallery': true,
                'callback': null,
                'styling': true,
                'items': 12,
                'items_per_row': 1,
                'margin': 1
            });

        if($('#pinterest-list-container').length !== 0)
            $('#pinterest-list-container').dcPinterestFeed({
            id:  $('#pinterest-list-container').data('account'),
        });

        const account = $('#twitter-list-container').data('account');

        if($('#twitter-list-container').length !== 0)
        $.ajax({
            url: `/wp-json/social_bar/twitter/feed?user=${account}`
        })  .done(function( data ) {
            const result = data.map(d => {
                return `<li>
                            <div>
                                <div class="picture">
                                    <a href="https://twitter.com/${d.user.screen_name}"><img src="${d.user.profile_image_url_https}"/></a>    
                                </div>
                                <div class="text">
                                   <div class="header">
                                        <p><span class="name">${d.user.name}</span><span class="url_name">@${d.user.screen_name}</span></p>
                                        <p class="date"><span class="date">${d.created_at}</span></p>
                                    </div>
                                    <div class="body">
                                        <p>${d.text}</p>
                                    </div>
                                    <div class="footer">
                                        <div><p><i class="fas fa-retweet"></i>${d.retweet_count}</p></div>
                                        <div><p><i class="fas fa-heart"></i>${d.favorite_count}</p></div>
                                    </div>
                                </div>
                            </div>
                        </li>`
            });
            $('#twitter-list-container').append(`<ul>${result.join('')}</ul>`);
        });
    });
})(jQuery);