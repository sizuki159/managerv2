var OnLoad = {
    funcs: [],

    add: function(func) {
        OnLoad.funcs.push(func);
    },

    execute: function() {
        var self = OnLoad;

        window.onload = function() {
            for (var i = 0; i < self.funcs.length; ++i) {
                var func = self.funcs[i];

                if (typeof func === "function")
                    func();
            }
        };
    }
};

OnLoad.execute();