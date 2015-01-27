$(function(){
    $('#search_btn').click(function(){
        var searchStr = $('#search_input').val();
        $.ajax({
            type: 'GET',
            url: '/api/find_student/' + searchStr,
            success: function (data) {
                if (data.hasOwnProperty('students')) {
                    var newText = '';
                    var len = data.students.length;
                    for(var i = 0; i < len; i++){
                        var curr = data.students[i];
                        newText += '<p>' + curr.name + ' <a href="/api/set_student/' + curr.id + '">Выбрать</a></p>'
                    }
                    $('#search_results').html(newText);
                }
            },
            dataType: 'json'
        });
    });
});
