var CheckboxCheckAll = {
    form:      null,
    checkall:  null,
    textCount: null,

    countCheckedItem:  0,
    countCheckboxItem: 0,

    onInitForm: function(idForm, idCheckboxAll, idElementTextCount) {
        CheckboxCheckAll.form      = document.getElementById(idForm);
        CheckboxCheckAll.checkall  = document.getElementById(idCheckboxAll);
        CheckboxCheckAll.textCount = document.getElementById(idElementTextCount);

        if (CheckboxCheckAll.isValidateVariable() == false)
            return;

        CheckboxCheckAll.updateCountCheckboxStatus();
        CheckboxCheckAll.putCountCheckedItem();
        CheckboxCheckAll.updateCheckboxAllCheckedStatus();
    },

    onCheckAll: function() {
        if (CheckboxCheckAll.isValidateVariable() == false)
            return;

        var checked = CheckboxCheckAll.checkall.checked == true;

        for (var i = 0; i < CheckboxCheckAll.form.elements.length; ++i) {
            var element = CheckboxCheckAll.form.elements[i];

            if (element.type && element.type === "checkbox" && element !== CheckboxCheckAll.checkall)
                element.checked = checked;
        }

        CheckboxCheckAll.updateCountCheckboxStatus();
        CheckboxCheckAll.putCountCheckedItem();
        CheckboxCheckAll.updateCheckboxAllCheckedStatus();
    },

    onCheckItem: function(idCheckboxItem) {
        var checkitem = document.getElementById(idCheckboxItem);

        if (typeof checkitem === "undefined" || checkitem === null || CheckboxCheckAll.isValidateVariable() == false)
            return;

        if (checkitem.type && checkitem.type === "checkbox")
            checkitem.cheked = checkitem.checked == true;

        CheckboxCheckAll.updateCountCheckboxStatus();
        CheckboxCheckAll.putCountCheckedItem();
        CheckboxCheckAll.updateCheckboxAllCheckedStatus();
    },

    putCountCheckedItem: function() {
        if (CheckboxCheckAll.isValidateVariable() == false)
            return;

        if (CheckboxCheckAll.countCheckedItem > 0)
            CheckboxCheckAll.textCount.innerHTML = "(" + CheckboxCheckAll.countCheckedItem + ")";
        else
            CheckboxCheckAll.textCount.innerHTML = null;
    },

    updateCountCheckboxStatus: function() {
        if (CheckboxCheckAll.isValidateVariable() == false)
            return;

        CheckboxCheckAll.countCheckboxItem = 0;
        CheckboxCheckAll.countCheckedItem  = 0;

        for (var i = 0; i < CheckboxCheckAll.form.elements.length; ++i) {
            var element = CheckboxCheckAll.form.elements[i];

            if (element.type && element.type === "checkbox" && element !== CheckboxCheckAll.checkall) {
                if (element.checked)
                    CheckboxCheckAll.countCheckedItem++;

                CheckboxCheckAll.countCheckboxItem++;
            }
        }
    },

    updateCheckboxAllCheckedStatus: function() {
        if (CheckboxCheckAll.isValidateVariable() == false)
            return;

        if (CheckboxCheckAll.countCheckedItem <= 0)
            CheckboxCheckAll.checkall.checked = false;
        else if (CheckboxCheckAll.countCheckedItem >= CheckboxCheckAll.countCheckboxItem)
            CheckboxCheckAll.checkall.checked = true;
    },

    isValidateVariable: function() {
        if (typeof CheckboxCheckAll.form === "undefined" || typeof CheckboxCheckAll.checkall === "undefined" || typeof CheckboxCheckAll.textCount === "undefined")
            return false;

        if (CheckboxCheckAll.form === null || CheckboxCheckAll.checkall === null || CheckboxCheckAll.textCount === null)
            return false;

        return true;
    }

};