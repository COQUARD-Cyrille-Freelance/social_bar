(function($){

    PinterestFeedObject = function(el, options) {
        this.create(el, options);
    };

    $.extend(PinterestFeedObject.prototype, {

        create: function(el, options) {

            // Set the default options
            this.defaults = {
                id: '', // enter username, pinterest list ID or search keyword
                results: 10, // number of results to display
                tweetId: '' // twitter username for share links
            };

            var o = $.extend(true,this.defaults,options);

            // Add ul tag to target element
            $(el).append('<ul class="stream"></ul>');

            // Set Pinterest RSS url using Google Feed API
            var cp = o.id.split('/'),
                url = `/wp-json/social_bar/pinterest/feed/?user=${cp[0]}${cp.length > 1 ? '&board='+ cp[1] : ''}`

            // jQuery AJAX call
            jQuery.ajax({
                url: url,
                cache: true,
                dataType: 'json',
                success: function(a){
                    $.each(a, function(i,item){
                        if(i < o.results){
                            var d = item.date,
                                img = item.description,
                                html = '<li>' + img;

                            // Get time since
                            d = d != '' ? html += '<span class="date">'+nicetime(new Date(d).getTime())+'</span></li>' : '' ;
                        }

                        // Add pinterest feed items to stream
                        $('.stream',el).append(html);
                    });
                },
                complete: function(){

                    // Code to open new popup window for share links + open other links in new browser tab
                    $('.stream a',el).click(function(e){
                        if($(this).parent().hasClass('section-share')){
                            var u = $(this).attr('href');
                            window.open(u,'sharer','toolbar=0,status=0,width=626,height=436');
                            return false;
                        } else {
                            if(external){this.target = '_blank';}
                        }
                    });

                    // jQuery Isotope function
                    /*var $c = $('.stream',el);
                    $c.imagesLoaded( function(){
                        $c.isotope({itemSelector : 'li'});
                        $('li',$c).fadeIn();
                    });*/
                }
            });
        }
    });

    $.fn.dcPinterestFeed = function(options, callback){
        var d = {};
        this.each(function(){
            var s = $(this);
            d = s.data("pinterestfeed");
            if (!d){
                d = new PinterestFeedObject(this, options, callback);
                s.data("pinterestfeed", d);
            }
        });
        return d;
    };

    // Creates facebook & twitter share links
    function shareLink(st,sq,tweetId){
        var sq = encodeURIComponent(sq), st = encodeURIComponent(st);
        var s = '<a href="http://www.facebook.com/sharer.php?u='+sq+'&t='+st+'" class="share-facebook"></a>';
        s += '<a href="https://twitter.com/share?url='+sq+'&text='+st+'&via='+tweetId+'" class="share-twitter"></a>';
        return s;
    }

    // Function to convert date to relative date
    function nicetime(a){
        var d = Math.round((+new Date - a) / 1000), fuzzy;
        var chunks = new Array();
        chunks[0] = [60 * 60 * 24 * 365 , 'year'];
        chunks[1] = [60 * 60 * 24 * 30 , 'month'];
        chunks[2] = [60 * 60 * 24 * 7, 'week'];
        chunks[3] = [60 * 60 * 24 , 'day'];
        chunks[4] = [60 * 60 , 'hr'];
        chunks[5] = [60 , 'min'];
        var i = 0, j = chunks.length;
        for (i = 0; i < j; i++) {
            s = chunks[i][0];
            n = chunks[i][1];
            if ((xj = Math.floor(d / s)) != 0)
                break;
        }
        fuzzy = xj == 1 ? '1 '+n : xj+' '+n+'s';
        if (i + 1 < j) {
            s2 = chunks[i + 1][0];
            n2 = chunks[i + 1][1];
            if ( ((xj2 = Math.floor((d - (s * xj)) / s2)) != 0) )
                fuzzy += (xj2 == 1) ? ' + 1 '+n2 : ' + '+xj2+' '+n2+'s';
        }
        fuzzy += ' ago';
        return fuzzy;
    }

})(jQuery);