$(document).ready(function() {
    $('.popup_video a.popup').magnificPopup({type:'inline'});

    $('.bind_video').click(function(){
        bindVideo($(this).val());
    });
});

function bindVideo(id) {
    $.post(
        "/video/bind/",
        {
            video_id: id
        },
        function(resp, textStatus, jqXHR)
        {
            switch (resp['status']) {
                case 'ok':
                    alert('video add');
                    break;
                case 'already':
                    alert('video already add');
                    break;
                case 'bad':
                    alert('failed to add video');
                    break;
                default:
                    break;
            }
        }
    );
}
