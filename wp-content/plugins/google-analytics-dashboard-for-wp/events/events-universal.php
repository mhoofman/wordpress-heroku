<script type="text/javascript">
(function($){
    $(window).load(function() {
            $('a').filter(function() {
                return this.href.match(/.*\.(<?php echo get_option("ga_event_downloads");?>)/);
            }).click(function(e) {
                ga('send','event', 'download', 'click', this.href);
            });
            $('a[href^="mailto"]').click(function(e) {
                ga('send','event', 'email', 'send', this.href);
             });
            var loc = location.host.split('.');
            while (loc.length > 2) { loc.shift(); }
            loc = loc.join('.');
            var localURLs = [
                              loc,
                              '<?php echo get_option("ga_default_domain");?>'
                            ];
            $('a[href^="http"]').filter(function() {
                for (var i = 0; i < localURLs.length; i++) {
                    if (this.href.indexOf(localURLs[i]) == -1) return this.href;
                }
            }).click(function(e) {
                ga('send','event', 'outbound', 'click', this.href);
            });
    });
})(jQuery);
</script>