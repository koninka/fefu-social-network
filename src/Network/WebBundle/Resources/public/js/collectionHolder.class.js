function CollectionHolder(startIndex, container) {
    this.index = startIndex;
    this.domContainer = $(container);
    this.form_prototype = this.domContainer.data('prototype');
    this.init();
}

CollectionHolder.prototype.init = function() {
    var addBtn = $('<button>Add</button>');
    var th = this;

    addBtn.on('click', function(e) {
        e.preventDefault();

        var newForm = $(th.form_prototype.replace(/__name__/g, th.index++));
        newForm.append(th.createDeleteBtn());

        th.domContainer.append(newForm);
    });

    this.domContainer.prepend(addBtn);

    this.domContainer.children('div').each(function(idx, elem) {
        $(elem).append(th.createDeleteBtn());
    });
};

CollectionHolder.prototype.createDeleteBtn = function() {
    var btn = $('<button>Delete</button>');

    btn.on('click', function(e) {
        e.preventDefault();

        $(this).parent().remove();
    });

    return btn;
};
