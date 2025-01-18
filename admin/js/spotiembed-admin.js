jQuery(document).ready(function($) {
    // Add any JavaScript functionality needed for the admin page here

    // Handle preview button click
    $('.preview-spotify-embed').on('click', function() {
        var $button = $(this);
        var $preview = $button.siblings('.spotify-preview');
        var spotifyUrl = $('#spotiembed_url').val();
        
        if (!spotifyUrl) {
            return;
        }

        // Extract Spotify URI and type from URL
        var match = spotifyUrl.match(/open\.spotify\.com\/(track|album|artist|playlist)\/([a-zA-Z0-9]+)/);
        if (!match) {
            alert('Please enter a valid Spotify URL');
            return;
        }

        var type = match[1];
        var id = match[2];
        
        // Create embed iframe
        var iframe = $('<iframe>')
            .attr('src', 'https://open.spotify.com/embed/' + type + '/' + id)
            .attr('width', '100%')
            .attr('height', '352')
            .attr('frameborder', '0')
            .attr('allowtransparency', 'true')
            .attr('allow', 'encrypted-media');

        $preview.html(iframe).show();
    });
});
