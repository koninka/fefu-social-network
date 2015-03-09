function CollectionHolder(startIndex, container, add_, delete_) {
    this.index = startIndex;
    this.domContainer = $(container);
    this.form_prototype = this.domContainer.data('prototype');
    this.init(add_, delete_);
}

CollectionHolder.prototype.initSearchSuggestors = function (container) {
    container
        .find('input[class*="vdolgah_searchable_field"]')
        .each(function (idx, elem) {
            new SearchSuggestor(elem);
        });
};

CollectionHolder.prototype.init = function(add_, delete_) {
    var addBtn = $('<button class="btn btn-green btn-small">'+add_+'</button>');
    var th = this;

    addBtn.on('click', function(e) {
        e.preventDefault();

        var newForm = $(th.form_prototype.replace(/__name__/g, th.index++));
        newForm.append(th.createDeleteBtn(delete_));

        th.domContainer.append(newForm);

        th.initSearchSuggestors(newForm);
        initDatePickers(newForm);
    });

    this.domContainer.prepend(addBtn);

    this.domContainer.children('div').each(function(idx, elem) {
        $(elem).append(th.createDeleteBtn(delete_));
    });

    this.initSearchSuggestors(this.domContainer);
};

CollectionHolder.prototype.createDeleteBtn = function(delete_) {
    var btn = $('<button class="btn btn-red btn-small">'+delete_+'</button>');

    btn.on('click', function(e) {
        e.preventDefault();

        $(this).parent().remove();
    });

    return btn;
};
