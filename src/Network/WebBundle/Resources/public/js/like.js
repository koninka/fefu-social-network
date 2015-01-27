function AddModalForm(data)
{
    var id = data['id'];
    var user = data['users'];
    var name = "#modal_form_" + id;
    $( name ).display = "block";
    $(name).html('');
    $( name ).append($('<div></div>'));
    if (user.length > 0) {
        for(i = 0; i < user.length; i++) {
            $( name ).append($('<p></p>').append(
            $('<a href="' + Routing.generate('user_profile', {id: user[i]['id']}) + '">' + user[i]['text'] + '</a>')));
        }
    } else {
        $(name).append('<p> For this answer, no one voted </p>');
    }
    dialog = $(name).dialog({
        autoOpen: false,
        modal: true,
    });
    dialog.dialog( "open" );
}

function addLike(postEdit, id, clas)
{
    postEdit.show();
    var like = postEdit.find('#like');
    var count = postEdit.find('#count');
    new_count = function (data)
    {
        if (data['status'] == 'ok'){
           var like = $('#' + data['id'] + '_like');
           var count = like.find('#count');
           count.html(data['count']);
        }
    };
    var divModal = $('<div id="modal_form_' + id+ '" style="display:none"></div>');
    postEdit.append(divModal);
    $.post(
            Routing.generate(
                'like_count' ),
            JSON.stringify({
                id: id,
                class:clas
            }),
            new_count
    );
    new_like = function (data)
    {
        if (data['status'] == 'ok'){
           var like = $('#' + data['id'] + '_like');
           var count = like.find('#count');
           count.html(data['count']);
        }
    }
    $(like).on('click', function (e) {
        $.post(
            Routing.generate(
                'like' ),
            JSON.stringify({
                id: id,
                class:clas
            }),
            new_like
        );
    });
     $(count).click(function(e) {
        e.preventDefault();
        $.post(
        Routing.generate(
            'like_user'),
            JSON.stringify({
                id: id,
                class: clas
        }),
        AddModalForm
        );
    });
}

function mediaLike (post_id) {
    var postEdit = $('.like').clone();
    postEdit.show();
    var msgContainer = $('#' + post_id + '_like');
    addLike(postEdit, post_id, 'media');
    msgContainer.append(postEdit);
}


