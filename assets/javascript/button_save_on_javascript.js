var ButtonSaveOnJavascript = {
    buttonElement: null,

    isKeyCtrl: true,
    isKeyS:    false,

    WHICH_KEY_CTRL: 17,
    WHICH_KEY_S:    83,

    addEventSave: function() {
        var self = ButtonSaveOnJavascript;

        self.buttonElement = document.getElementById("button-save-on-javascript");

        if (self.buttonElement === null || typeof self.buttonElement === "undefined" || typeof self.buttonElement.click === "undefined")
            return false;

        window.addEventListener("keydown", function(event) {
            if (event.which) {
                if (event.which === self.WHICH_KEY_S && event.ctrlKey && event.ctrlKey == true) {
                    self.isKeyS = true;
                    event.preventDefault();

                    return false;
                }
            }

            return true;
        });

        window.addEventListener("keyup", function(event) {
            if (event.which && event.which == self.WHICH_KEY_CTRL) {
                self.isKeyCtrl = false;

                if (self.isKeyS == true) {
                    self.isKeyS = false;
                    self.saveActionEvent();
                    event.preventDefault();

                    return true;
                }
            }

            event.preventDefault();
            return false;
        });
    },

    saveActionEvent: function() {
        if (ButtonSaveOnJavascript.buttonElement !== null && typeof ButtonSaveOnJavascript.buttonElement !== "undefined" && ButtonSaveOnJavascript.buttonElement.click)
            ButtonSaveOnJavascript.buttonElement.click();
    }
};

if (typeof OnLoad !== "undefined" && OnLoad.add)
    OnLoad.add(ButtonSaveOnJavascript.addEventSave);
else
    window.onload = ButtonSaveOnJavascript.addEventSave;