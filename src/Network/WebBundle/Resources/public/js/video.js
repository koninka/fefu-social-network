var canSubmit = true;

$(document).ready(function() {
    $('.popup_video a.popup').magnificPopup({type:'inline'});

    $('.bind_video').click(function(){
        bindVideo($(this).val());
    });

    $('.delete_video').click(function(){
        deleteVideo($(this).val(), $(this));
    });

    $('#add_video').submit(function(){
        if (canSubmit) {
            canSubmit = false;
            return true;
        }
        return false;
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

function deleteVideo(id, sender) {
    $.post(
        "/video/delete/",
        {
            video_id: id
        },
        function(resp, textStatus, jqXHR)
        {
            switch (resp['status']) {
                case 'ok':
                    sender.parent().parent().remove();
                    alert('video delete');
                    break;
                case 'no_rights':
                    alert('you can\'t delete this video');
                    break;
                case 'bad':
                    alert('failed to delete video');
                    break;
                default:
                    break;
            }
        }
    );
}
