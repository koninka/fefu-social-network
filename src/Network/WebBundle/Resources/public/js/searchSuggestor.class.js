function SearchSuggestor(field) {
    this.field = $(field);
    $(this.field).autocomplete({
        source: this.performJsonRequest.bind(this),
        minLength: 2
    });
}

SearchSuggestor.prototype.getLastWord = function (str) {
    var substr = str.match(/_[^_]+$/)[0];
    var pos = substr.indexOf(' ');
    return (pos > 0) ? substr.substr(1, pos - 1) : substr.substr(1);
};

SearchSuggestor.prototype.performJsonRequest = function (request, response) {
    $.post(
        Routing.generate('user_profile_json'),
        JSON.stringify({
            what : this.getLastWord(this.field.attr('id')),
            by : this.getLastWord(this.field.attr('class')),
            val : request['term']
        }),
        function (data, status, jqXHR) {
            var res = [];

            if (data['result'] === 'ok') {
                var records = data['data'];

                for (var i = 0; i < records.length; ++i) {
                    var record = JSON.parse(records[i]);
                    res.push(record['name']);
                }
            }
            response(res);
        }
    );
};
