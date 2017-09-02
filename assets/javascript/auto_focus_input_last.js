var AutoFocusInputLast = {
    elements: [],

    execute: function() {
        var inputs    = document.getElementsByTagName("input");
        var textareas = document.getElementsByTagName("textarea");

        if (typeof inputs !== "undefined" && inputs.length > 0) {
            for (var i = 0; i < inputs.length; ++i) {
                var element = inputs[i];

                if (element.type) {
                    var type = element.type;

                    if (type === "text" || type === "password" || type === "number") {
                        AutoFocusInputLast.elements.push(element);
                        AutoFocusInputLast.addListener(element);
                    }
                }
            }
        }

        if (typeof textareas !== "undefined" && textareas.length > 0) {
            for (var i = 0; i < textareas.length; ++i) {
                AutoFocusInputLast.elements.push(textareas[i]);
                AutoFocusInputLast.addListener(textareas[i]);
            }
        }

        AutoFocusInputLast.autoFocusOnHastagUrl(true);
    },

    addListener: function(element) {
        element.addEventListener("focus", function() {
            AutoFocusInputLast.addHastagToForm(element);
        });
    },

    autoFocusOnHastagUrl: function() {
        var hrefLocation = window.location.href;

        if (hrefLocation !== null && hrefLocation.length > 0) {
            var hastagIndexHrefLocation = hrefLocation.lastIndexOf("#");
            var hastagValue             = null;

            if (hastagIndexHrefLocation !== -1) {
                hastagValue = hrefLocation.substring(hastagIndexHrefLocation + 1);

                AutoFocusInputLast.cleanAutoFocusElement();
                AutoFocusInputLast.putAutoFocusElementName(hastagValue, true);
            } else if (AutoFocusInputLast.elements.length && AutoFocusInputLast.elements.length > 0) {
                AutoFocusInputLast.cleanAutoFocusElement();
                AutoFocusInputLast.putAutoFocusElement(AutoFocusInputLast.elements[0]);
            }
        }
    },

    addHastagToForm: function(element) {
        if (element.form && typeof element.form !== "undefined") {
            var actionForm   = null;
            var hrefLocation = window.location.href;
            var nameElement  = element.name;

            if (element.form.getAttribute)
                actionForm = element.form.getAttribute("action");
            else if (element.form.getAttributeNode)
                actionForm = element.form.getAttributeNode("action");

            if (actionForm !== null && actionForm.length > 0) {
                var hastagIndexAction = actionForm.lastIndexOf("#");

                if (hastagIndexAction !== -1)
                    actionForm = actionForm.substring(0, hastagIndexAction);
            }

            if (hrefLocation !== null && hrefLocation.length > 0) {
                var hastagIndexHrefLocation = hrefLocation.lastIndexOf("#");

                if (hastagIndexHrefLocation !== -1)
                    hrefLocation = hrefLocation.substring(0, hastagIndexHrefLocation);
            }

            if (typeof nameElement !== "undefined" && nameElement !== null)
                nameElement = element.tagName.toLowerCase() + "_" + nameElement;

            element.form.action  = actionForm + "#" + nameElement;
            window.location.href = actionForm + "#" + nameElement;

            AutoFocusInputLast.cleanAutoFocusElement();
            AutoFocusInputLast.putAutoFocusElement(element);
        }
    },

    cleanAutoFocusElement: function() {
        if (typeof AutoFocusInputLast.elements === "undefined" && AutoFocusInputLast.elements.length <= 0)
            return;

        for (var i = 0; i < AutoFocusInputLast.elements.length; ++i) {
            var element = AutoFocusInputLast.elements[i];

            if (element.removeAttribute)
                element.removeAttribute("autofocus", "autofocus");
            else if (element.removeAttributeNode)
                element.removeAttributeNode("autofocus", "autofocus");
        }
    },

    putAutoFocusElement: function(element, isFocusOnHastag) {
        if (element.setAttribute)
            element.setAttribute("autofocus", "autofocus");
        else if (element.setAttributeNode)
            element.setAttributeNode("autofocus", "autofocus");

        element.focus();

        if (isFocusOnHastag) {
            var scrollTopElement = document.documentElement || document.body;
            var scrollTop        = window.pageYOffset || scrollTopElement.scrollTop;
            var elementOffsetTop = element.offsetTop + element.form.offsetTop;

            if (scrollTop > elementOffsetTop)
                scrollTopElement.scrollTop = elementOffsetTop;
        }
    },

    putAutoFocusElementName: function(name, isFocusOnHastag) {
        if (typeof AutoFocusInputLast.elements === "undefined" && AutoFocusInputLast.elements.length <= 0)
            return;

        for (var i = 0; i < AutoFocusInputLast.elements.length; ++i) {
            var element     = AutoFocusInputLast.elements[i];
            var nameElement = element.name;

            if (typeof nameElement !== "undefined" && nameElement !== null)
                nameElement = element.tagName.toLowerCase() + "_" + nameElement;
            else
                nameElement = element.tagName.toLowerCase();

            if (nameElement === name)
                AutoFocusInputLast.putAutoFocusElement(element, isFocusOnHastag);
        }
    }
};

if (typeof OnLoad !== "undefined" && OnLoad.add)
    OnLoad.add(AutoFocusInputLast.execute);
else
    window.onload = AutoFocusInputLast.execute;