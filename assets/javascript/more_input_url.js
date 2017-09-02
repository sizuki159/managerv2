var MoreInputUrl = {
    getAttribute: function(element, name) {
        if (element.getAttribute)
            return element.getAttribute(name);
        else if (element.getAttributeNode)
             return element.getAttributeNode(name);
    },

    setAttribute: function(element, name, value, checkExists) {
        if (element.setAttribute)
            element.setAttribute(name, value);
        else if (element.setAttributeNode)
            element.setAttributeNode(name, value);
    },

    removeAttribute: function(element, name) {
        if (element.removeAttribute)
            element.removeAttribute(name);
        else if (element.removeAttributeNode)
            element.removeAttributeNode(name);
    },

    onAddMoreInputUrl: function(idTemplateInputUrl, namePrefix) {
        var elementTemplate = document.getElementById(idTemplateInputUrl);
        var elementClone    = elementTemplate.cloneNode(true);
        var parentElement   = elementTemplate.parentElement;

        var childNodes       = parentElement.childNodes;
        var childNodesLength = childNodes.length;

        var attributeNameTemplate = MoreInputUrl.getAttribute(elementTemplate, "name");
        var indexCurrentTemplate  = 0;

        if (attributeNameTemplate != null && typeof attributeNameTemplate !== "undefined") {
            if (attributeNameTemplate.value)
                attributeNameTemplate = attributeNameTemplate.value;

            if (attributeNameTemplate != null && attributeNameTemplate.indexOf(namePrefix, 0) === 0) {
                indexCurrentTemplate = attributeNameTemplate.substring(namePrefix.length);
                indexCurrentTemplate = parseInt(indexCurrentTemplate);;
            } else {
                indexCurrentTemplate = 0;
            }

            var inputClone = elementClone.getElementsByTagName("input");

            for (var i = 0; i < inputClone.length; ++i) {
                var input    = inputClone[i];
                var inputNew = document.createElement("input");

                MoreInputUrl.setAttribute(inputNew, "type",        MoreInputUrl.getAttribute(input, "type"));
                MoreInputUrl.setAttribute(inputNew, "name",        MoreInputUrl.getAttribute(input, "name"));
                MoreInputUrl.setAttribute(inputNew, "placeholder", MoreInputUrl.getAttribute(input, "placeholder"));

                elementClone.replaceChild(inputNew, input);
            }

            var valueIndexCurrent = namePrefix + (indexCurrentTemplate + 1);

            MoreInputUrl.setAttribute(elementClone, "name", valueIndexCurrent);
        }

        MoreInputUrl.removeAttribute(elementTemplate, "id");

        for (var i = childNodesLength - 1; i >= 0; --i) {
            var child     = childNodes[i];
            var childName = MoreInputUrl.getAttribute(child, "name");

            if (childName != null && typeof childName !== "undefined") {
                if (childName.value)
                    childName = childName.value;

                if (childName.indexOf(namePrefix, 0) === 0) {
                    parentElement.insertBefore(elementClone, child.nextSibling);

                    break;
                }
            }
        }
    }
};
