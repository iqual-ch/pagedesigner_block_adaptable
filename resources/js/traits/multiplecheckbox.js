(function ($, Drupal) {

    function init(editor) {
        const TraitManager = editor.TraitManager;
        TraitManager.addType('multiplecheckbox',
            Object.assign({}, TraitManager.defaultTrait,
                {
                    events: {
                        click: 'handleClick'
                    },

                    afterInit: function () {
                        var value = this.model.get('value');
                        if ((!value || value == '')) {
                            if (this.model.attributes.additional.preview) {
                                this.model.set('value', this.model.attributes.additional.preview);
                            } else {
                                this.model.set('value', Object.keys(this.model.attributes.additional.options));
                            }
                        }
                    },
                    handleClick: function (e) {
                        if (e.toElement.checked != undefined) {
                            if (e.toElement.checked == true) {
                                var value = this.model.get('value');
                                value[e.toElement.value] = true;
                                this.model.set('value', value);
                            } else {
                                var value = this.model.get('value');
                                value[e.toElement.value] = false;
                                this.model.set('value', value);
                            }
                        }
                    },


                    getInputEl: function () {
                        if (!this.inputEl) {
                            var div = jQuery('<div class="gjs-field-multiplecheckbox-options">');
                            var options = this.model.attributes.additional.options;
                            var value = this.model.get('value');
                            var name = this.model.attributes.additional.name;
                            var values = this.model.attributes.additional.values;
                            for (var key in options) {
                                var optionContainer = $('<div></div>');
                                var option = $('<input type="checkbox" name="' + name + '[]" value="' + key + '">');

                                var label = $('<label>' + options[key] + '</label>');
                                optionContainer.append(option);
                                optionContainer.append(label);
                                div.append(optionContainer);
                                if (typeof this.model.get('value') == 'undefined') {
                                    this.model.set('value', values);
                                    if (values[key]) {
                                        option.prop('checked', true)
                                    } else if (key == value) {
                                        option.prop('checked', true)
                                    }
                                }
                                else if (this.model.get('value') != "") {
                                    if (this.model.get('value')[key]) {
                                        option.prop('checked', true)
                                    }
                                }
                            }
                            this.inputEl = div.get(0);
                        }
                        return this.inputEl;
                    },
                    getRenderValue: function (value) {
                        if (typeof this.model.get('value') == 'undefined') {
                            return value;
                        }
                        return this.model.get('value');
                    },
                    setTargetValue: function (value) {
                        this.model.set('value', value);
                    },
                    setInputValue: function (value) {
                        if (value) {
                            this.model.set('value', value);
                            var i;
                            var options = $(this.inputEl)[0].getElementsByTagName('input');
                            for (i = 0; i < options.length; i++) {
                                if (value[options[i].value] == true) {
                                    options[i].checked = true;
                                }
                                else {
                                    options[i].checked = false;
                                }
                            }
                        }
                    }
                })
        );
    }

    Drupal.behaviors.pagedesigner_trait_multiplecheckbox = {
        attach: function (context, settings) {
            $(document).on('pagedesigner-init-traits', function (e, editor) {
                init(editor);
            });
        }
    };

})(jQuery, Drupal);
