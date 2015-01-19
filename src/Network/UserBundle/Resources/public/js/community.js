$(document).ready(function() {
     var dialog, request, participant;
      dialog = $( "#modal_form" ).dialog({
        autoOpen: false,
        height: 300,
        width: 600,
        modal: true,
     });
     participant = $( "#modal_form_participant" ).dialog({
        autoOpen: false,
        height: 300,
        width: 600,
        modal: true,
     });
     request = $( "#modal_form_application" ).dialog({
        autoOpen: false,
        height: 300,
        width: 600,
        modal: true,
     });
    $('#create_community').click(function(e) {
        e.preventDefault();
        dialog.dialog( "open" );
    });
    $('#invite').click(function(e) {
        e.preventDefault();
        dialog.dialog( "open" );
    });
    $('#participants').click(function(e) {
        e.preventDefault();
        participant.dialog( "open" );
    });
    $('#application').click(function(e) {
        e.preventDefault();
        request.dialog( "open" );
    });
 });

